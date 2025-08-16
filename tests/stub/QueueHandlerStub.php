<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp\tests\stub;

use Override;
use Revolt\EventLoop;
use kuaukutsu\queue\core\QueueContext;
use kuaukutsu\queue\core\TaskInterface;

final readonly class QueueHandlerStub implements TaskInterface
{
    public function __construct(
        public int $id,
        public string $name,
        private TaskWriter $writer,
    ) {
    }

    #[Override]
    public function handle(QueueContext $context): void
    {
        $id = $this->id;
        $name = $this->name;
        $writer = $this->writer;
        $delay = ($id % 5) === 0 ? 2. : 0.5;
        // check  non-blocking
        EventLoop::delay($delay, static function () use ($id, $name, $context, $writer): void {
            $writer->print($id, $name, $context);
        });
    }
}
