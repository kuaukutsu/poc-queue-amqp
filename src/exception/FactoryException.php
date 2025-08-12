<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp\exception;

use Throwable;
use RuntimeException;
use Psr\Container\ContainerExceptionInterface;

final class FactoryException extends RuntimeException implements ContainerExceptionInterface
{
    /**
     * @param non-empty-string $message
     */
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
