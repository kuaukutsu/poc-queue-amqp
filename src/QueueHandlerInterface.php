<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp;

use Throwable;

interface QueueHandlerInterface
{
    /**
     * @throws Throwable
     */
    public function handle(QueueContext $context): void;
}
