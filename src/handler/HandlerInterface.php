<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp\handler;

use Throwable;
use kuaukutsu\poc\queue\amqp\interceptor\InterceptorInterface;
use kuaukutsu\poc\queue\amqp\QueueMessage;

interface HandlerInterface
{
    public function withInterceptors(InterceptorInterface ...$interceptors): self;

    /**
     * @throws Throwable
     */
    public function handle(QueueMessage $message): void;
}
