<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp\exception;

use Throwable;
use RuntimeException;
use kuaukutsu\poc\queue\amqp\QueueSchemaInterface;

final class QueueDeclareException extends RuntimeException
{
    public function __construct(QueueSchemaInterface $schema, Throwable $previous)
    {
        parent::__construct(
            sprintf(
                '[%s] queue declare is failed: %s',
                $schema->getRoutingKey(),
                $previous->getMessage()
            ),
            0,
            $previous,
        );
    }
}
