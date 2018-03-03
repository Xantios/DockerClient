<?php

namespace Xantios\Docker;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7 as GuzzleStream;
use Psr\Http\Message\ResponseInterface;

class Client {

    public $client;

    public function __construct($uri = null) {

        $unixSock = [];

        // Unix socket support is a bit iffy in Guzzle 6.
        // We have to provide some sort of HTTP URL, and overload that with a CURLOPT_UNIX_SOCKET_PATH afterwards.
        if (!$uri) {
            $uri      = 'http://foo.tld';
            $unixSock = [
                CURLOPT_UNIX_SOCKET_PATH => '/var/run/docker.sock',
            ];
        }

        $this->client = new Guzzle([
            'base_uri' => $uri,
            'timeout'  => '4',
            'curl'     => $unixSock,
        ]);
    }

    public function all() {

        $body       = json_decode((string)$this->client->get('/containers/json')->getBody());
        $containers = [];

        foreach ($body as $container) {
            $containers[] = new Container($container->Id, $this->client);
        }

        return $containers;
    }

    public function container($id) {
        return new Container($id, $this->client);
    }

    public function create($conf) {
        # https://docs.docker.com/engine/api/v1.36/#operation/ContainerCreate
    }

    public function subscribe($callback) {

        // Guzzle is being a bit iffy here, so lets get oldschool :)
        $sock = 'unix:////var/run/docker.sock';
        $sock = stream_socket_client($sock, $errno, $errstr);

        if (!$sock) {
            $callback('error', 'Cant subscribe to docker host [' . $errno . ' ' . $errstr . ']');
            return false;
        }

        $request = [
            'GET /events HTTP/1.1',
            'Host: localhost',
            'Connection: stream',
            'User-Agent: PHP',
            'Accept: text',
            'Accept-Charset: utf-8',
        ];

        $request = implode("\r\n", $request);
        fwrite($sock, $request . "\r\n\r\n");

        // Get HTTP header out of the buffer
        fread($sock, 1024 * 4);

        $disconnected = false;

        while (!$disconnected) {

            if (feof($sock)) {
                $disconnected = true;
                continue;
            }

            $data = fread($sock, 4096);

            if (strlen($data) > 0) {

                // Remove enters
                $data = str_replace("\r", '', $data);
                $data = str_replace("\n", '', $data);

                // Strip off the first 3 bytes (which is some sort of code?)
                $data = substr($data, 3);

                $event = json_decode($data);

                if ($event) {
                    $callback($event);
                }

                $data = '';
            }
        }


    }

}
