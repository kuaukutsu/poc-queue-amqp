<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp;

use stdClass;
use DI\Container;
use Thesis\Amqp\Config;
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
