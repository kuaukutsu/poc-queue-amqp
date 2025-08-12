<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp;

interface QueueSchemaInterface
{
    /**
     * @return non-empty-string
     */
    public function getRoutingKey(): string;
}
