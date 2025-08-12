<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp;

use DI\Container;
use Thesis\Amqp\Config;
use kuaukutsu\poc\queue\amqp\tests\stub\QueueHandlerStub;
use kuaukutsu\poc\queue\amqp\tests\stub\QueueSchemaStub;

use function kuaukutsu\poc\queue\amqp\test\argument;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$schema = QueueSchemaStub::from((string)argument('schema', 'low'));
echo 'publisher run: ' . $schema->getRoutingKey() . PHP_EOL;

$container = new Container();
$builder = (new QueueBuilder($container))
    ->withConfig(
        new Config(
            urls: ['rabbitmq:5672'],
            user: 'rabbit',
            password: 'rabbit',
        )
    );

$publisher = $builder->buildPublisher();

$task = new QueueTask(
    target: QueueHandlerStub::class,
    arguments: [
        'id' => 1,
        'name' => 'test name',
    ],
);

// the EOInterceptor must process when reading/consume the task
$publisher
    ->push($schema, $task);
$publisher
    ->push($schema, $task);

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
