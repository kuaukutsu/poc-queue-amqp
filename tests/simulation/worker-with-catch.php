<?php

/**
 * Consumer.
 * @var Builder $builder bootstrap.php
 */

declare(strict_types=1);

use kuaukutsu\poc\queue\amqp\Builder;
use kuaukutsu\poc\queue\amqp\tests\stub\QueueSchemaStub;
use kuaukutsu\poc\queue\amqp\tests\stub\TryCatchInterceptor;

use function Amp\trapSignal;
use function kuaukutsu\poc\queue\amqp\tests\argument;

require dirname(__DIR__) . '/bootstrap.php';

$schema = QueueSchemaStub::from((string)argument('schema', 'low'));
echo 'consumer run: ' . $schema->getRoutingKey() . PHP_EOL;

$consumer = $builder
    ->withCatch(
        static function (?string $message, Throwable $exception): void {
            echo sprintf('M: %s, error: %s', $message, $exception->getMessage());
        }
    )
    ->withInterceptors(
        new TryCatchInterceptor(),
    )
    ->buildConsumer();

$consumer->consume($schema);

/** @noinspection PhpUnhandledExceptionInspection */
trapSignal([SIGTERM, SIGINT]);
$consumer->disconnect();
exit(0);
