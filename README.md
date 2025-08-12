План:
- сделать компоненту которую мы можем использовать в условном контуре onion/infrastracture
- интерфейсы для queue / pub-sub / tcp

Как собираюсь использовать?
- как библиотеку, подключаем в проект, используем интерфейсы (QueueInterface / PublisherInterface / TCPInterface) 
и классы-контейнеры (Message)

Например:
Кейс: отправить сообщение в очередь и запустить обработчик.
Обработчик: ссылка на него передаём через контейнер.
Мы можем запустить несколько обработчиков, а значит неплохо было бы иметь примитивный супервизор. Но с другой стороны,
такой функционал уже лучше на стороне самого приложения.

Нужно будет задавать конфигурацию, допустим через `new Config(...)`. 
Т.е мы обычным образом через DI конфигурируем имплементацию Queue. Интерфейс не нужен, ибо он будет в самом приложении, на уровне домена.
Вероятно, к Queue у нас должен быть QueueWorker, который позволит имплементировать код воркера/консумера + конфигурирование.
Воркеров может быть много:
- несколько схем, на каждую схему по отдельной имплементации
- плюс на каждую имплементацию может быть группа обработчиков, и им нужен будет примитивный супервизор

Из чего состоит Сообщение:
- uuid
- handler
- arguments

Из чего состоит Контейнер: (нужно избавиться)
- Сообщение
- Контекст

```php
$queue = new Queue(
    config: new Config(),
);
```

```php
$queue = new Queue(
    config: new Config(),
    handler: new Pipeline(
        ExecutionTimeInterceptor::class,
    ),
);
```

```php
$queue = new Queue(
    config: new Config(),
    handler: (new Pipeline($factory))
        ->withInterceptors(
            ExecutionTimeInterceptor::class,
        ),
);
```


```php
$queue->push(
    QueueSchema::DEFAULT,
    new QueueTask(
        target: 'handler::class',
        arguments: ['one' => 1, 'two' => 2],
        context: ?new QueueContext(...)
    ),
);
```

Как будто есть:
- Queue, push(...) и consume(...)
- QueueTask, описывает обработчик задачи и аргументы.
- QueueContext: int attempt, string request_id, string created_at, QueueSchema schema.
- QueueSchema, через какой канал отправить, точнее в какую очередь.

Зачем нам контекст?
Зафиксировать служебную информацию: 
- время создания, 
- кол-во попыток (если делаем ретраи), 
- и зафиксировать через какой канал передавали.
В целом пока нет возможности представить себе задачу, чтобы пришлось определять контекст на уровне создания задачи.

```php
$queue
    ->catch(
        static function (Exception $exception, DeliveryMessage $delivery): void {
            $delivery->reject();
        }
    )
    ->consume(QueueSchema::DEFAULT);

$queue->consume(
    QueueSchema::DEFAULT,
    catch: static function (Exception $exception, DeliveryMessage $delivery): void {
        $delivery->reject();
    }
);

$queue->consume(
    QueueSchema::DEFAULT,
    static function (DeliveryMessage $delivery): void {
        $delivery->ack();
    },
    static function (Exception $exception, DeliveryMessage $delivery): void {
        $delivery->reject();
    },
);

$queue->consume(
    QueueSchema::DEFAULT,
    callback: static function (DeliveryMessage $delivery, ?Exception $exception = null): void {
        $exception === null ? $delivery->ack() : $delivery->reject();
    },
);
```

Подключение.
```php

// нужен какой-то минимальный DI
$container = new \DI\Container();

// конфигурируем экземпляр Queue
$queue = new Queue($config, $interceptors, $container); // какое-то гавно

// в целом-то то же самое, но читается легче и можно будет что-то довесить если что, нет жёсткой привязки на зависимости 
$queue = new QueueBuilder($container)
    ->withInterceptors($interceptors)
    ->build($config); // <- QueueInterface -> push(task, schema) & consume(schema, callback)

```
