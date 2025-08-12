<?php

declare(strict_types=1);

use DI\Container;
use Thesis\Amqp\Config;
use kuaukutsu\poc\queue\amqp\QueueBuilder;

require dirname(__DIR__) . '/vendor/autoload.php';

$container = new Container();
$builder = (new QueueBuilder($container))
    ->withConfig(
        new Config(
            urls: ['rabbitmq:5672'],
            user: 'rabbit',
            password: 'rabbit',
        )
    );
