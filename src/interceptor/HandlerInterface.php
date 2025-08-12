<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp\interceptor;

use Throwable;
use kuaukutsu\poc\queue\amqp\internal\ConsumeMessage;

interface HandlerInterface
{
    public function withInterceptors(InterceptorInterface ...$interceptors): self;

    /**
     * @throws Throwable
     */
    public function handle(ConsumeMessage $message): void;
}
