<?php

declare(strict_types=1);

namespace Rector\PHPStan\Type;

use PHPStan\Type\ObjectType;

final class AliasedObjectType extends ObjectType
{
    /**
     * @var string
     */
    private $fullyQualifiedClass;

    public function __construct(string $alias, string $fullyQualifiedClass)
    {
        parent::__construct($alias);
        $this->fullyQualifiedClass = $fullyQualifiedClass;
    }

    public function getFullyQualifiedClass(): string
    {
        return $this->fullyQualifiedClass;
    }
}
