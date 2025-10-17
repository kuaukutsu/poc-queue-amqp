<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\redis\benchmarks;

use Thesis\Amqp\Config;
use DI\Container;
use kuaukutsu\poc\queue\amqp\Builder;
use kuaukutsu\poc\queue\amqp\internal\FactoryProxy;
use kuaukutsu\poc\queue\amqp\tests\stub\QueueHandlerStub;
use kuaukutsu\poc\queue\amqp\tests\stub\QueueSchemaStub;
use kuaukutsu\queue\core\PublisherInterface;
use kuaukutsu\queue\core\QueueContext;
use kuaukutsu\queue\core\QueueTask;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;

#[Revs(10)]
#[Iterations(5)]
final class PublisherBench
{
    private PublisherInterface $publisher;

    public function __construct()
    {
        $builder = (new Builder(new FactoryProxy(new Container())))
            ->withConfig(
                new Config(
                    urls: ['rabbitmq:5672'],
                    user: 'rabbit',
                    password: 'rabbit',
                )
            );

        $this->publisher = $builder->buildPublisher();
    }

    public function benchAsWhile(): void
    {
        $schema = QueueSchemaStub::high;

        // range
        foreach (range(1, 100) as $item) {
            $this->publisher
                ->push(
                    $schema,
                    new QueueTask(
                        target: QueueHandlerStub::class,
                        arguments: [
                            'id' => $item,
                            'name' => 'bench range',
                        ],
                    ),
                    QueueContext::make($schema)
                        ->withExternal(['requestId' => $item])
                );
        }
    }

    public function benchAsBatch(): void
    {
        $schema = QueueSchemaStub::high;

        $batch = [];
        foreach (range(1, 100) as $item) {
            $batch[] = new QueueTask(
                target: QueueHandlerStub::class,
                arguments: [
                    'id' => $item,
                    'name' => 'bench batch',
                ],
            );
        }

        $this->publisher
            ->pushBatch(
                $schema,
                $batch,
                QueueContext::make($schema)
                    ->withExternal(['requestId' => 100])
            );
    }
}
