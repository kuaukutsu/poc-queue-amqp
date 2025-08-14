<?php

/**
 * Publisher, make task with exception.
 * @var Builder $builder bootstrap.php
 */

declare(strict_types=1);

use kuaukutsu\queue\core\QueueTask;
use kuaukutsu\poc\queue\amqp\Builder;
use kuaukutsu\poc\queue\amqp\tests\stub\QueueSchemaStub;

use function kuaukutsu\poc\queue\amqp\tests\argument;

require dirname(__DIR__) . '/bootstrap.php';

$schema = QueueSchemaStub::from((string)argument('schema', 'low'));
echo 'publisher run: ' . $schema->getRoutingKey() . PHP_EOL;

$task = new QueueTask(
    /** @phpstan-ignore argument.type */
    target: stdClass::class,
    arguments: [
        'id' => 1,
        'name' => 'test name',
    ],
);

$builder
    ->buildPublisher()
    ->push($schema, $task);
