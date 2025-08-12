<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp\tests\stub;

use Override;
use kuaukutsu\poc\queue\amqp\QueueSchemaInterface;

enum QueueSchemaStub: string implements QueueSchemaInterface
{
    case low = 'low';
    case high = 'high';

    #[Override]
    public function getRoutingKey(): string
    {
        return $this->name;
    }
}
