<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp\interceptor;

use Throwable;
use kuaukutsu\poc\queue\amqp\handler\HandlerInterface;
use kuaukutsu\poc\queue\amqp\QueueMessage;

interface InterceptorInterface
{
    /**
     * @throws Throwable
     */
    public function intercept(QueueMessage $message, HandlerInterface $handler): void;
}
