<?php
use Psr\Log\LoggerInterface;
use Klepto\Scratcher;
use GuzzleHttp\Client;
use Monolog\Logger;

include __DIR__ . "/../vendor/autoload.php";

$container = require('container.php');

//dump(new Scratcher($container->get(Client::class), $container->get(LoggerInterface::class)));

//dump($container->get("GuzzleHttp\Client"));

// dump(DI\get(Scratcher::class)->isResolvable($container));

// $classReflection = new ReflectionClass(Scratcher::class);
// dump($classReflection);
// dump($classReflection->getConstructor());

// array_map(
//     function ($item) {
//         dump($item->getClass());
//     },
//     $classReflection->getConstructor()->getParameters()
// );

dump($container->get(Scratcher::class));
