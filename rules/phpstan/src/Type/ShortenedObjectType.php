<?php

declare(strict_types=1);

namespace Rector\PHPStan\Type;

use PHPStan\Type\ObjectType;

final class ShortenedObjectType extends ObjectType
{
    /**
     * @var string
     */
    private $fullyQualifiedName;

    public function __construct(string $shortName, string $fullyQualifiedName)
    {
        parent::__construct($shortName);

        $this->fullyQualifiedName = $fullyQualifiedName;
    }

    public function getShortName(): string
    {
        return $this->getClassName();
    }

    public function getFullyQualifiedName(): string
    {
        return $this->fullyQualifiedName;
    }
}
