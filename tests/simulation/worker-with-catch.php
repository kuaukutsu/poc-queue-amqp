#!/usr/bin/env php
<?php

/**
 * Consumer.
 * @var QueueBuilder $builder bootstrap.php
 */

declare(strict_types=1);

use Thesis\Amqp\DeliveryMessage;
use kuaukutsu\poc\queue\amqp\QueueBuilder;
use kuaukutsu\poc\queue\amqp\tests\stub\QueueSchemaStub;
use kuaukutsu\poc\queue\amqp\tests\stub\TryCatchInterceptor;

use function Amp\trapSignal;
use function kuaukutsu\poc\queue\amqp\tests\argument;

require dirname(__DIR__) . '/bootstrap.php';

$schema = QueueSchemaStub::from((string)argument('schema', 'low'));
echo 'consumer run: ' . $schema->getRoutingKey() . PHP_EOL;

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

/** @noinspection PhpUnhandledExceptionInspection */
trapSignal([SIGTERM, SIGINT]);
