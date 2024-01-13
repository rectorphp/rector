<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Enum;

class ToManyMappings
{
    /**
     * @var class-string[]
     */
    public const TO_MANY_CLASSES = ['Doctrine\\ORM\\Mapping\\OneToMany', 'Doctrine\\ORM\\Mapping\\ManyToMany'];
}
