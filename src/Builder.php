<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp;

use Closure;
use Override;
use RuntimeException;
use Thesis\Amqp\Client;
use Thesis\Amqp\Config;
use kuaukutsu\queue\core\handler\FactoryInterface;
use kuaukutsu\queue\core\handler\HandlerInterface;
use kuaukutsu\queue\core\handler\Pipeline;
use kuaukutsu\queue\core\interceptor\InterceptorInterface;
use kuaukutsu\queue\core\BuilderInterface;

/**
 * @api
 */
final class Builder implements BuilderInterface
{
    private Config $config;

    private HandlerInterface $handler;

    private ?Closure $catch = null;

    public function __construct(
        FactoryInterface $factory,
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

    #[Override]
    public function withCatch(Closure $catch): BuilderInterface
    {
        $clone = clone $this;
        $clone->catch = $catch;
        return $clone;
    }


    #[Override]
    public function withInterceptors(InterceptorInterface ...$interceptor): self
    {
        $clone = clone $this;
        $clone->handler = $this->handler->withInterceptors(...$interceptor);
        return $clone;
    }

    /**
     * @throws RuntimeException
     */
    #[Override]
    public function buildPublisher(): Publisher
    {
        return (new Publisher(new Client($this->config)))->withConfirm();
    }

    #[Override]
    public function buildConsumer(): Consumer
    {
        return new Consumer(new Client($this->config), $this->handler, $this->catch);
    }
}
