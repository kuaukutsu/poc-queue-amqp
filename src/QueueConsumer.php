<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp;

use Throwable;
use Thesis\Amqp\Client;
use Thesis\Amqp\Channel;
use Thesis\Amqp\DeliveryMessage;
use kuaukutsu\poc\queue\amqp\exception\QueueDeclareException;
use kuaukutsu\poc\queue\amqp\exception\QueueConsumeException;
use kuaukutsu\poc\queue\amqp\interceptor\HandlerInterface;
use kuaukutsu\poc\queue\amqp\internal\ConsumeMessage;

/**
 * @api
 */
final readonly class QueueConsumer
{
    private Channel $channel;

    public function __construct(
        Client $client,
        private HandlerInterface $handler,
    ) {
        $this->channel = $client->channel();
    }

    /**
     * @param ?callable(DeliveryMessage,Throwable):void $catch
     * @throws QueueDeclareException
     * @throws QueueConsumeException
     */
    public function consume(QueueSchemaInterface $schema, ?callable $catch = null): void
    {
        try {
            $this->channel->queueDeclare(
                queue: $schema->getRoutingKey(),
                durable: true,
            );
        } catch (Throwable $exception) {
            throw new QueueDeclareException($schema, $exception);
        }

        $handler = $this->handler;

        try {
            $this->channel->qos(prefetchCount: 1);
            $this->channel->consume(
                callback: static function (DeliveryMessage $delivery) use ($handler, $catch): void {
                    try {
                        $handler->handle(
                            ConsumeMessage::makeFromMessage($delivery->message),
                        );
                    } catch (Throwable $exception) {
                        if (is_callable($catch)) {
                            $catch($delivery, $exception);
                            return;
                        }
                    }

                    $delivery->ack();
                },
                queue: $schema->getRoutingKey(),
            );
        } catch (Throwable $exception) {
            throw new QueueConsumeException($schema, $exception);
        }
    }
}
