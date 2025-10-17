## Библиотека для обработки задач через внешнюю очередь

[![PHP Version Require](http://poser.pugx.org/kuaukutsu/poc-queue-amqp/require/php)](https://packagist.org/packages/kuaukutsu/poc-queue-amqp)
[![Latest Stable Version](https://poser.pugx.org/kuaukutsu/poc-queue-amqp/v/stable)](https://packagist.org/packages/kuaukutsu/poc-queue-amqp)
[![License](http://poser.pugx.org/kuaukutsu/poc-queue-amqp/license)](https://packagist.org/packages/kuaukutsu/poc-queue-amqp)
[![Psalm Level](https://shepherd.dev/github/kuaukutsu/poc-queue-amqp/level.svg)](https://shepherd.dev/github/kuaukutsu/poc-queue-amqp)
[![Psalm Type Coverage](https://shepherd.dev/github/kuaukutsu/poc-queue-amqp/coverage.svg)](https://shepherd.dev/github/kuaukutsu/poc-queue-amqp)

Очередь: **RabbitMQ**  

Драйвер для работы: **thesis/amqp**,
pure asynchronous (fiber based) strictly typed full-featured PHP driver for AMQP 0.9.1 protocol.

Дополнительно: support for **interceptors**, 
which can be used to add functionality to the application without modifying the core code of the application.

### Installation

```shell
composer require kuaukutsu/poc-queue-amqp
```

### Usage

```php
$container = new Container();
$builder = (new Builder(new FactoryProxy($container)))
    ->withConfig(
        new Config(
            urls: ['rabbitmq:5672'],
            user: 'rabbit',
            password: 'rabbit',
        )
    );

// publisher
$publisher = $builder->buildPublisher();

// consumer
$consumer = $builder->buildConsumer();
```

Publish with confirmation

```php
// $schema instanceof QueueSchemaInterface

$publisher
    ->withConfirm()
    ->push(
        $schema,
        new QueueTask(
            target: QueueHandlerStub::class,
            arguments: [
                'id' => 21273,
                'name' => 'test handler',
            ],
        ),
    );
```

Consume with ExactlyOnce interceptor

```php
// $schema instanceof QueueSchemaInterface

$redis = createRedisClient('redis://redis:6379');
$builder
    ->withInterceptors(
        new ExactlyOnceInterceptor(
            new RedisCache($redis),
            new RedisMutex($redis),
        ),
    )
    ->buildConsumer()
    ->consume($schema);
```

Ссылки:
- https://github.com/thesis-php/amqp
- https://github.com/rabbitmq/rabbitmq-tutorials/tree/main/php-thesis
- https://spiral.dev/docs/framework-interceptors/current/en

Benchmark

```
PHPBench (1.4.1) running benchmarks...
with configuration file: /benchmark/phpbench.json
with PHP version 8.3.22, xdebug ✔, opcache ❌

\kuaukutsu\poc\queue\redis\benchmarks\PublisherBench

    benchAsWhile............................I4 - Mo167.758ms (±55.89%)
    benchAsBatch............................I4 - Mo11.905ms (±3.25%)

Subjects: 2, Assertions: 0, Failures: 0, Errors: 0
+----------------+--------------+-----+------+-----+----------+-----------+---------+
| benchmark      | subject      | set | revs | its | mem_peak | mode      | rstdev  |
+----------------+--------------+-----+------+-----+----------+-----------+---------+
| PublisherBench | benchAsWhile |     | 10   | 5   | 6.405mb  | 167.758ms | ±55.89% |
| PublisherBench | benchAsBatch |     | 10   | 5   | 6.216mb  | 11.905ms  | ±3.25%  |
+----------------+--------------+-----+------+-----+----------+-----------+---------+
```

With igbinary
```
PHPBench (1.4.1) running benchmarks...
with configuration file: /benchmark/phpbench.json
with PHP version 8.3.22, xdebug ✔, opcache ✔

\kuaukutsu\poc\queue\redis\benchmarks\PublisherBench

    benchAsWhile............................I4 - Mo167.864ms (±44.56%)
    benchAsBatch............................I4 - Mo11.772ms (±0.78%)

Subjects: 2, Assertions: 0, Failures: 0, Errors: 0
+----------------+--------------+-----+------+-----+----------+-----------+---------+
| benchmark      | subject      | set | revs | its | mem_peak | mode      | rstdev  |
+----------------+--------------+-----+------+-----+----------+-----------+---------+
| PublisherBench | benchAsWhile |     | 10   | 5   | 2.788mb  | 167.864ms | ±44.56% |
| PublisherBench | benchAsBatch |     | 10   | 5   | 2.499mb  | 11.772ms  | ±0.78%  |
+----------------+--------------+-----+------+-----+----------+-----------+---------+
```
