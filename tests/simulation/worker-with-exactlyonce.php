<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp;

use Amp\Redis\RedisCache;
use Amp\Redis\Sync\RedisMutex;
use DI\Container;
use Thesis\Amqp\Config;
use kuaukutsu\poc\queue\amqp\interceptor\ExactlyOnceInterceptor;
use kuaukutsu\poc\queue\amqp\tests\stub\QueueSchemaStub;

use function Amp\Redis\createRedisClient;
use function Amp\trapSignal;
use function kuaukutsu\poc\queue\amqp\test\argument;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$schema = QueueSchemaStub::from((string)argument('schema', 'low'));
echo 'consumer run: ' . $schema->getRoutingKey() . PHP_EOL;

$builder = (new QueueBuilder(new Container()))
    ->withConfig(
        new Config(
            urls: ['rabbitmq:5672'],
            user: 'rabbit',
            password: 'rabbit',
        )
    );

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

trapSignal([\SIGTERM, \SIGINT]);
