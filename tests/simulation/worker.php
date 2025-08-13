#!/usr/bin/env php
<?php

/**
 * Consumer.
 * @var QueueBuilder $builder bootstrap.php
 */

declare(strict_types=1);

use kuaukutsu\poc\queue\amqp\QueueBuilder;
use kuaukutsu\poc\queue\amqp\tests\stub\QueueSchemaStub;

use function Amp\trapSignal;
use function kuaukutsu\poc\queue\amqp\tests\argument;

require dirname(__DIR__) . '/bootstrap.php';

$schema = QueueSchemaStub::from((string)argument('schema', 'low'));
echo 'consumer run: ' . $schema->getRoutingKey() . PHP_EOL;

$builder
    ->buildConsumer()
    ->consume($schema);

/** @noinspection PhpUnhandledExceptionInspection */
trapSignal([SIGTERM, SIGINT]);
