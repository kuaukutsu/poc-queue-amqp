<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp\internal;

use LogicException;
use Thesis\Amqp\Message;
use kuaukutsu\poc\queue\amqp\QueueContext;
use kuaukutsu\poc\queue\amqp\QueueTask;

/**
 * @psalm-internal kuaukutsu\poc\queue\amqp
 */
final readonly class ConsumeMessage
{
    public function __construct(
        public QueueTask $task,
        public QueueContext $context,
    ) {
    }

    /**
     * @throws LogicException if Message violates protocol
     */
    public static function makeFromMessage(Message $message): self
    {
        $container = unserialize(
            $message->body,
            [
                'allowed_classes' => true,
            ]
        );

        if (
            is_array($container) && isset($container[0], $container[1])
            && $container[0] instanceof QueueTask
            && $container[1] instanceof QueueContext
        ) {
            return new self($container[0], $container[1]);
        }

        throw new LogicException('Message must contain QueueTask and QueueContext.');
    }
}
