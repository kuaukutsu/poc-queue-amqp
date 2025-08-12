<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp\internal;

use kuaukutsu\poc\queue\amqp\exception\UnsupportedException;

/**
 * @psalm-internal kuaukutsu\poc\queue\amqp
 */
trait SerializableDeprecated
{
    /**
     * @throws UnsupportedException
     */
    public function serialize(): never
    {
        throw new UnsupportedException();
    }

    /**
     * @throws UnsupportedException
     */
    public function unserialize(string $data): never
    {
        throw new UnsupportedException();
    }
}
