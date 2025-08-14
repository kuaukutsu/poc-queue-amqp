<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp;

use DI\FactoryInterface;
use Thesis\Amqp\Client;
use Thesis\Amqp\Config;
use kuaukutsu\queue\core\handler\HandlerInterface;
use kuaukutsu\queue\core\handler\Pipeline;
use kuaukutsu\queue\core\interceptor\InterceptorInterface;
use kuaukutsu\poc\queue\amqp\internal\FactoryProxy;

/**
 * @api
 */
final class QueueBuilder
{
    private Config $config;

    private HandlerInterface $handler;

    public function __construct(
        FactoryInterface $factory,
        ?HandlerInterface $handler = null,
    ) {
        $this->config = new Config();
        $this->handler = $handler ?? new Pipeline(new FactoryProxy($factory));
    }

    public function withConfig(Config $config): self
    {
        $clone = clone $this;
        $clone->config = $config;
        return $clone;
    }

    public function withInterceptors(InterceptorInterface ...$interceptor): self
    {
        $clone = clone $this;
        $clone->handler = $this->handler->withInterceptors(...$interceptor);

        return $clone;
    }

    public function buildPublisher(): Publisher
    {
        return new Publisher(new Client($this->config));
    }

    public function buildConsumer(): Consumer
    {
        return new Consumer(new Client($this->config), $this->handler);
    }
}
