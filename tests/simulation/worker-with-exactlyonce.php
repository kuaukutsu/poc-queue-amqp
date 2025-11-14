<?php

/**
 * Consumer.
 * @var Builder $builder bootstrap.php
 */

declare(strict_types=1);

use Amp\Redis\RedisCache;
use Amp\Redis\Sync\RedisMutex;
use kuaukutsu\poc\queue\amqp\Builder;
use kuaukutsu\poc\queue\amqp\tests\stub\QueueSchemaStub;
use kuaukutsu\queue\core\interceptor\ExactlyOnceInterceptor;

use function Amp\Redis\createRedisClient;
use function Amp\trapSignal;
use function kuaukutsu\poc\queue\amqp\tests\argument;

require dirname(__DIR__) . '/bootstrap.php';

$schema = QueueSchemaStub::from((string)argument('schema', 'low'));
echo 'consumer run: ' . $schema->getRoutingKey() . PHP_EOL;

$redis = createRedisClient('redis://redis:6379');
$consumer = $builder
    ->withInterceptors(
        new ExactlyOnceInterceptor(
            new RedisCache($redis),
            new RedisMutex($redis),
        ),
    )
    ->buildConsumer($schema);

$consumer->consume();

/** @noinspection PhpUnhandledExceptionInspection */
trapSignal([SIGTERM, SIGINT]);
$consumer->disconnect();
exit(0);
