<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp\tests\stub;

use kuaukutsu\queue\core\QueueContext;

final readonly class TaskWriter
{
    public function print(int $id, string $name, QueueContext $context): void
    {
        echo sprintf(
            "task: %d, %s, route: %s, date: %s\r\n",
            $id,
            $name,
            $context->routingKey,
            $context->createdAt,
        );
    }
}
