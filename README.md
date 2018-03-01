# Docker client

About
------------------
There are a few ways to connect to docker using PHP. but i did not really liked any of them for my particular purpose. 
So i did what any developer would do, i wrote my own solution   

Although inspired by laravel, you don't have to use this in Laravel. it should be framework agnostic.

Installation
-----------------

```shell
composer require xantios/docker 
```

Usage 
-----------------

###### Get it up and running fast 
~~~php
<?php
    
use Xantios\Docker\Client;
    
$client = new Client();
$all = $client->all(); // Gets all containers (as neat little objects)
   
foreach($all as $container) {
   var_dump($container); // Have a peak in the container
   
   // You can also call some methods on the containers
   $ports = $container->ports();
   var_dump($ports);
}   
~~~

###### Some nice output and more advanced usage example 
```php
<?php

    use Xantios\Docker\Client;

        $client     = new Client();
        $containers = $client->all();

        foreach ($containers as $container) {

            print "Name :: " . $container->name() . " \r\n";

            print "Exposed :: \r\n";
            foreach ($container->exposed() as $exposed) {
                print '     ' . $exposed['protocol'] . '/' . $exposed['port'] . "\r\n";
            }
            print "\r\n";

            print "Network :: \r\n";
            print "IP:  " . $container->ip() . "\r\n";
            print "Mac: " . $container->mac() . "\r\n";
            print "\r\n";

            print "Portmap:: \r\n";
            $ports = $container->ports();
            foreach ($ports as $port) {
                print $port['container']['protocol'] . '/' . $port['container']['ip'] . ':' . $port['container']['port']
                    . '      =>      ' .
                    $port['host']['protocol'] . '/' . $port['host']['ip'] . ':' . $port['host']['port']
                    . "\r\n";
            }
            print "\r\n";

            print "Env :: \r\n";
            foreach ($container->env() as $k => $v) {
                print $k . '        ' . $v . "\r\n";
            }


            print "\r\n";
            print "-------------------------------------------------\r\n";
```

