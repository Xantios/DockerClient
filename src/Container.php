<?php

namespace Xantios\Docker;

class Container {

    private $client, $config;
    protected $id;

    public function __construct($id, $client) {

        $this->client = $client;
        $this->id     = $id;

        $this->config = json_decode((string)$this->client->get('/containers/' . $id . '/json')->getBody());
    }

    public function config() {
        return $this->config->Config;
    }

    public function name() {
        return substr($this->config->Name, 1);
    }

    public function env() {
        return $this->keyValueParser(
            $this->config->Config->Env
        );
    }

    public function mounts() {
        return $this->config->Mounts;
    }

    public function exposed() {

        $ports = $this->config->Config->ExposedPorts;

        $output = [];

        foreach ($ports as $port => $x) {

            $item = explode('/', $port);

            $output[] = [
                'port'     => $item[0],
                'protocol' => $item[1],
            ];

        }

        return $output;
    }

    public function ports() {

        $ret   = [];
        $ports = $this->config->NetworkSettings->Ports;

        foreach ($ports as $portKey => $portValue) {

            $protocol  = explode('/', $portKey)[1];
            $localPort = explode('/', $portKey)[0];

            $ret[] = [
                'container' => [
                    'protocol' => $protocol,
                    'port'     => $localPort,
                    'ip'       => $this->ip(),
                ],
                'host'      => [
                    'protocol' => $protocol,
                    'port'     => $portValue[0]->HostPort,
                    'ip'       => $portValue[0]->HostIp,
                ],
            ];
        }

        return $ret;
    }

    public function ip() {
        return $this->config->NetworkSettings->IPAddress;
    }

    public function gateway() {
        return $this->config->NetworkSettings->Gateway;
    }

    public function mac() {
        return $this->config->NetworkSettings->MacAddress;
    }

    private function keyValueParser($data) {

        $ret = [];

        foreach ($data as $item) {

            $tmp = explode('=', $item);

            if (count($tmp) >= 1) {
                $ret[$tmp[0]] = $tmp[1];
            }
            else {
                $ret[] = $tmp[0];
            }
        }

        return $ret;
    }

}
