<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp;

use Closure;
use Override;
use Throwable;
use Thesis\Amqp\Channel;
use Thesis\Amqp\Client;
use Thesis\Amqp\DeliveryMessage;
use kuaukutsu\queue\core\exception\QueueConsumeException;
use kuaukutsu\queue\core\exception\QueueDeclareException;
use kuaukutsu\queue\core\handler\HandlerInterface;
use kuaukutsu\queue\core\ConsumerInterface;
use kuaukutsu\queue\core\QueueMessage;
use kuaukutsu\queue\core\SchemaInterface;

/**
 * @api
 */
final readonly class Consumer implements ConsumerInterface
{
    private Channel $channel;

    public function __construct(
        private Client $client,
        private HandlerInterface $handler,
        private ?Closure $catch = null,
    ) {
        $this->channel = $client->channel();
    }

    /**
     * @throws QueueDeclareException
     * @throws QueueConsumeException
     */
    #[Override]
    public function consume(SchemaInterface $schema): void
    {
        try {
            $this->channel->queueDeclare(
                queue: $schema->getRoutingKey(),
                durable: true,
            );
        } catch (Throwable $exception) {
            throw new QueueDeclareException($schema, $exception);
        }

        $catch = $this->catch;
        $handler = $this->handler;

        try {
            $this->channel->qos(prefetchCount: 1);
            $this->channel->consume(
                callback: static function (DeliveryMessage $delivery) use ($handler, $catch): void {
                    try {
                        $handler->handle(
                            QueueMessage::makeFromMessage($delivery->message->body)
                        );
                    } catch (Throwable $exception) {
                        if (is_callable($catch)) {
                            $catch($delivery->message->body, $exception);
                        }

                        // DLQ
                    }

                    $delivery->ack();
                },
                queue: $schema->getRoutingKey(),
            );
        } catch (Throwable $exception) {
            throw new QueueConsumeException($schema, $exception);
        }
    }

    #[Override]
    public function disconnect(): void
    {
        $this->channel->close();
        $this->client->disconnect();
    }
}
