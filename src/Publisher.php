<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp;

use Override;
use Throwable;
use RuntimeException;
use Thesis\Amqp\Client;
use Thesis\Amqp\Channel;
use Thesis\Amqp\DeliveryMode;
use Thesis\Amqp\Message;
use Thesis\Amqp\PublishConfirmation;
use Thesis\Amqp\PublishMessage;
use kuaukutsu\queue\core\exception\QueueDeclareException;
use kuaukutsu\queue\core\exception\QueuePublishException;
use kuaukutsu\queue\core\QueueContext;
use kuaukutsu\queue\core\QueueMessage;
use kuaukutsu\queue\core\QueueTask;
use kuaukutsu\queue\core\PublisherInterface;
use kuaukutsu\queue\core\SchemaInterface;

/**
 * @api
 */
final readonly class Publisher implements PublisherInterface
{
    private Channel $channel;

    public function __construct(private Client $client)
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
    #[Override]
    public function push(SchemaInterface $schema, QueueTask $task, ?QueueContext $context = null): string
    {
        $this->declareQueue($schema);

        try {
            $this->ensurePublished(
                $this->channel->publish(
                    message: $this->makeMessage($task, $context ?? QueueContext::make($schema)),
                    routingKey: $schema->getRoutingKey(),
                )
            );
        } catch (Throwable $exception) {
            throw new QueuePublishException($schema, $exception);
        }

        return $task->getUuid();
    }

    /**
     * @throws QueueDeclareException
     * @throws QueuePublishException
     */
    #[Override]
    public function pushBatch(SchemaInterface $schema, iterable $taskBatch, ?QueueContext $context = null): array
    {
        if ($taskBatch === []) {
            return [];
        }

        /**
         * @var non-empty-array<non-empty-string, PublishMessage> $messageList
         */
        $messageList = [];
        foreach ($taskBatch as $task) {
            $messageList[$task->getUuid()] = new PublishMessage(
                message: $this->makeMessage($task, $context ?? QueueContext::make($schema)),
                routingKey: $schema->getRoutingKey(),
            );
        }

        $this->declareQueue($schema);

        try {
            $this->channel->publishBatch(array_values($messageList));
        } catch (Throwable $exception) {
            throw new QueuePublishException($schema, $exception);
        }

        return array_keys($messageList);
    }

    public function disconnect(): void
    {
        $this->channel->close();
        $this->client->disconnect();
    }

    /**
     * @throws QueueDeclareException
     */
    private function declareQueue(SchemaInterface $schema): void
    {
        try {
            $this->channel->queueDeclare(
                queue: $schema->getRoutingKey(),
                durable: true,
            );
        } catch (Throwable $exception) {
            throw new QueueDeclareException($schema, $exception);
        }
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
            body: QueueMessage::makeMessage($task, $context),
            deliveryMode: DeliveryMode::Persistent,
        );
    }
}
