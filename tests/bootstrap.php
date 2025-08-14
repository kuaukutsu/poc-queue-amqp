<?php

declare(strict_types=1);

use DI\Container;
use Thesis\Amqp\Config;
use kuaukutsu\poc\queue\amqp\internal\FactoryProxy;
use kuaukutsu\poc\queue\amqp\Builder;

require dirname(__DIR__) . '/vendor/autoload.php';

$container = new Container();
$builder = (new Builder(new FactoryProxy($container)))
    ->withConfig(
        new Config(
            urls: ['rabbitmq:5672'],
            user: 'rabbit',
            password: 'rabbit',
        )
    );
