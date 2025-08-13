<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp\interceptor;

use Override;
use Amp\Cache\Cache;
use Amp\Sync\KeyedMutex;
use kuaukutsu\poc\queue\amqp\handler\HandlerInterface;
use kuaukutsu\poc\queue\amqp\QueueMessage;

final readonly class ExactlyOnceInterceptor implements InterceptorInterface
{
    /**
     * @param int $ttl Time in seconds. Default 10 min.
     */
    public function __construct(
        private Cache $cache,
        private KeyedMutex $mutex,
        private int $ttl = 600,
    ) {
    }

    #[Override]
    public function intercept(QueueMessage $message, HandlerInterface $handler): void
    {
        $lock = $this->mutex->acquire($message->task->getUuid());
        try {
            if ($this->cache->get($message->task->getUuid()) !== null) {
                return;
            }

            $this->cache->set($message->task->getUuid(), 1, $this->ttl);
        } finally {
            $lock->release();
        }

        $handler->handle($message);
    }
}
