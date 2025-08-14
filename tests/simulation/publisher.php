#!/usr/bin/env php
<?php

/**
 * Publisher.
 * @var QueueBuilder $builder bootstrap.php
 */

declare(strict_types=1);

use kuaukutsu\queue\core\QueueTask;
use kuaukutsu\poc\queue\amqp\QueueBuilder;
use kuaukutsu\poc\queue\amqp\tests\stub\QueueHandlerStub;
use kuaukutsu\poc\queue\amqp\tests\stub\QueueSchemaStub;

use function kuaukutsu\poc\queue\amqp\tests\argument;

require dirname(__DIR__) . '/bootstrap.php';

$schema = QueueSchemaStub::from((string)argument('schema', 'low'));
echo 'publisher run: ' . $schema->getRoutingKey() . PHP_EOL;

$publisher = $builder->buildPublisher();

$task = new QueueTask(
    target: QueueHandlerStub::class,
    arguments: [
        'id' => 1,
        'name' => 'test name',
    ],
);

// the EOInterceptor must process when reading/consume the task
$publisher->push($schema, $task);
$publisher->push($schema, $task);
$publisher->push($schema, $task);

// confirm
$publisher
    ->withConfirm()
    ->push(
        $schema,
        new QueueTask(
            target: QueueHandlerStub::class,
            arguments: [
                'id' => 21211,
                'name' => 'test confirm',
            ],
        ),
    );
