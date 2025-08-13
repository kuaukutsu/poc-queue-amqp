<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp;

use Thesis\Amqp\Client;
use Thesis\Amqp\Config;
use kuaukutsu\poc\queue\amqp\handler\HandlerInterface;
use kuaukutsu\poc\queue\amqp\handler\Pipeline;
use kuaukutsu\poc\queue\amqp\interceptor\InterceptorInterface;

/**
 * @api
 */
final class QueueBuilder
{
    private Config $config;

    private HandlerInterface $handler;

    public function __construct(
        \DI\FactoryInterface | FactoryInterface $factory,
        ?HandlerInterface $handler = null,
    ) {
        $this->config = new Config();
        $this->handler = $handler ?? new Pipeline($factory);
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

    public function buildPublisher(): QueuePublisher
    {
        return new QueuePublisher(new Client($this->config));
    }

    public function buildConsumer(): QueueConsumer
    {
        return new QueueConsumer(new Client($this->config), $this->handler);
    }
}
