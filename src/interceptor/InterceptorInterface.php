<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp\interceptor;

use Throwable;
use kuaukutsu\poc\queue\amqp\internal\ConsumeMessage;

interface InterceptorInterface
{
    /**
     * @throws Throwable
     */
    public function intercept(ConsumeMessage $message, HandlerInterface $handler): void;
}
