<?php

use Symfony\Component\Dotenv\Dotenv;
use DI\ContainerBuilder;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Psr\Log\LoggerInterface;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use MongoDB\Client as MongoDBClient;
use Cache\Adapter\MongoDB\MongoDBCachePool;
use Psr\Cache\CacheItemPoolInterface;
use Klepto\Scratcher;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

$config = [
    Client::class => DI\create(Client::class),
    Crawler::class => DI\create(Crawler::class),

    UidProcessor::class => DI\create(UidProcessor::class),
    GitProcessor::class => DI\create(GitProcessor::class),
    MemoryUsageProcessor::class => DI\create(MemoryUsageProcessor::class),
    ErrorLogHandler::class => DI\create(ErrorLogHandler::class),
    Logger::class => DI\create(Logger::class)->constructor(getenv("APP_NAME") ?: "klepto")
        ->method('pushHandler', DI\get(ErrorLogHandler::class))
        ->method('pushProcessor', DI\get(MemoryUsageProcessor::class))
        ->method('pushProcessor', DI\get(GitProcessor::class))
        ->method('pushProcessor', DI\get(UidProcessor::class))->lazy(),
    LoggerInterface::class => DI\get(Logger::class),

    MongoDBClient::class => DI\create(MongoDBClient::class)->constructor(getenv('MONGODB_URI')),
    MongoDBCachePool::class => DI\autowire(MongoDBCachePool::class)->lazy(),
    CacheItemPoolInterface::class => DI\get(MongoDBCachePool::class),

    Scratcher::class => DI\autowire(Scratcher::class)->lazy()

];

$builder = new ContainerBuilder();
$builder->useAnnotations(true);
$builder->addDefinitions($config);

return $builder->build();
