<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp\exception;

use LogicException;

final class UnsupportedException extends LogicException
{
    public function __construct(string $message = 'Unsupported operation.')
    {
        parent::__construct($message);
    }
}
