<?php

use Symfony\Component\DependencyInjection\ContainerInterface;

final class SkipGenericObject
{
    /**
     * @template T as object
     * @param class-string<T> $some_Type
     * @return T
     */
    public function getValue(ContainerInterface $container, string $some_Type): ?object
    {
        return $container->get($some_Type);
    }
}
