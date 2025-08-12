<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp;

use Throwable;
use RuntimeException;
use Thesis\Amqp\Client;
use Thesis\Amqp\Channel;
use Thesis\Amqp\DeliveryMode;
use Thesis\Amqp\Message;
use Thesis\Amqp\PublishConfirmation;
use kuaukutsu\poc\queue\amqp\exception\QueueDeclareException;
use kuaukutsu\poc\queue\amqp\exception\QueuePublishException;

/**
 * @api
 */
final readonly class QueuePublisher
{
    private Channel $channel;

    public function __construct(Client $client)
    {
        $this->channel = $client->channel();
    }

    /**
     * @throws RuntimeException
     */
    public function withConfirm(): self
    {
        $clone = clone $this;

        try {
            $clone->channel->confirmSelect();
        } catch (Throwable $exception) {
            throw new RuntimeException(
                message: $exception->getMessage(),
                previous: $exception,
            );
        }

        return $clone;
    }

    /**
     * @throws QueueDeclareException
     * @throws QueuePublishException
     */
    public function push(QueueSchemaInterface $schema, QueueTask $task, ?QueueContext $context = null): string
    {
        try {
            $this->channel->queueDeclare(
                queue: $schema->getRoutingKey(),
                durable: true,
            );
        } catch (Throwable $exception) {
            throw new QueueDeclareException($schema, $exception);
        }

        try {
            $this->ensurePublished(
                $this->channel->publish(
                    message: $this->makeMessage($task, $context ?? QueueContext::make($schema)),
                    routingKey: $schema->getRoutingKey(),
                )
            );
        } catch (Throwable $exception) {
            throw new QueuePublishException($task, $schema, $exception);
        }

        return $task->getUuid();
    }

    /**
     * @throws RuntimeException
     */
    private function ensurePublished(?PublishConfirmation $confirmation): void
    {
        if ($confirmation instanceof PublishConfirmation) {
            $confirmation
                ->await()
                ->ensurePublished();
        }
    }

    private function makeMessage(QueueTask $task, QueueContext $context): Message
    {
        return new Message(
            body: serialize(
                [
                    $task,
                    $context,
                ]
            ),
            deliveryMode: DeliveryMode::Persistent,
        );
    }
}
