<?php

namespace Xantios\Docker;

use GuzzleHttp\Client as Guzzle;

class Client {

    protected $client;

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

}
