<?php

declare(strict_types=1);

namespace kuaukutsu\poc\queue\amqp;

use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;

interface FactoryInterface
{
    /**
     * Resolves an entry by its name. If given a class name, it will return a new instance of that class.
     *
     * @template TClass
     * @param class-string<TClass> $name Entry name or a class name.
     * @param array $parameters Optional parameters to use to build the entry. Use this to force specific
     *                          parameters to specific values. Parameters not defined in this array will
     *                          be automatically resolved.
     *
     * @return TClass
     * @throws InvalidArgumentException The name parameter must be of type string.
     * @throws ContainerExceptionInterface Error while resolving the entry.
     */
    public function make(string $name, array $parameters = []): mixed;
}
