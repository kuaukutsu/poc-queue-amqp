<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp;

use Throwable;
use DI\Container;
use Thesis\Amqp\Config;
use Thesis\Amqp\DeliveryMessage;
use kuaukutsu\poc\queue\amqp\tests\stub\QueueSchemaStub;
use kuaukutsu\poc\queue\amqp\tests\stub\TryCatchInterceptor;

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

$builder
    ->withInterceptors(
        new TryCatchInterceptor(),
    )
    ->buildConsumer()
    ->consume(
        $schema,
        static function (DeliveryMessage $message, Throwable $exception): void {
            echo 'nack: ' . $exception->getMessage();
            $message->nack();
        }
    );

trapSignal([\SIGTERM, \SIGINT]);
