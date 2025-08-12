<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp\exception;

use Throwable;
use RuntimeException;
use kuaukutsu\poc\queue\amqp\QueueSchemaInterface;
use kuaukutsu\poc\queue\amqp\QueueTask;

final class QueuePublishException extends RuntimeException
{
    public function __construct(QueueTask $task, QueueSchemaInterface $schema, Throwable $previous)
    {
        parent::__construct(
            sprintf(
                '[%s] Task push to [%s] queue is failed: %s',
                $task->target,
                $schema->getRoutingKey(),
                $previous->getMessage()
            ),
            0,
            $previous,
        );
    }
}
