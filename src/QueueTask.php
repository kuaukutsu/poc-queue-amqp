<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp;

use Serializable;
use Ramsey\Uuid\Uuid;
use kuaukutsu\poc\queue\amqp\internal\SerializableDeprecated;

/**
 * @api
 */
final readonly class QueueTask implements Serializable
{
    use SerializableDeprecated;

    /**
     * @var non-empty-string format: UUID
     */
    private string $uuid;

    /**
     * @param class-string<QueueHandlerInterface> $target
     * @param array<non-empty-string, null|scalar|Serializable|array<null|scalar|Serializable>> $arguments
     */
    public function __construct(
        public string $target,
        public array $arguments = [],
    ) {
        $this->uuid = Uuid::uuid7()->toString();
    }

    /**
     * @return non-empty-string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function __serialize(): array
    {
        return [
            'uuid' => $this->uuid,
            'target' => $this->target,
            'arguments' => $this->arguments,
        ];
    }

    /**
     * @param array{
     *     "uuid": non-empty-string,
     *     "target": class-string<QueueHandlerInterface>,
     *     "arguments": array<non-empty-string, null|scalar|Serializable|array<null|scalar|Serializable>>,
     * } $data
     */
    public function __unserialize(array $data): void
    {
        $this->uuid = $data['uuid'];
        $this->target = $data['target'];
        $this->arguments = $data['arguments'];
    }
}
