#!/usr/bin/env php
<?php

/**
 * Consumer.
 * @var QueueBuilder $builder bootstrap.php
 */

declare(strict_types=1);

use Amp\Redis\RedisCache;
use Amp\Redis\Sync\RedisMutex;
use kuaukutsu\poc\queue\amqp\QueueBuilder;
use kuaukutsu\poc\queue\amqp\interceptor\ExactlyOnceInterceptor;
use kuaukutsu\poc\queue\amqp\tests\stub\QueueSchemaStub;

use function Amp\Redis\createRedisClient;
use function Amp\trapSignal;
use function kuaukutsu\poc\queue\amqp\test\argument;

require dirname(__DIR__) . '/bootstrap.php';

$schema = QueueSchemaStub::from((string)argument('schema', 'low'));
echo 'consumer run: ' . $schema->getRoutingKey() . PHP_EOL;


$redis = createRedisClient('redis://redis:6379');
$builder
    ->withInterceptors(
        new ExactlyOnceInterceptor(
            new RedisCache($redis),
            new RedisMutex($redis),
        ),
    )
    ->buildConsumer()
    ->consume($schema);

/** @noinspection PhpUnhandledExceptionInspection */
trapSignal([SIGTERM, SIGINT]);
