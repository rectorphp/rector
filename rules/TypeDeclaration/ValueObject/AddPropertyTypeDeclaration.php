<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\Type;
use Rector\Core\Validation\RectorAssert;

final class AddPropertyTypeDeclaration
{
    public function __construct(
        private readonly string $class,
        private readonly string $propertyName,
        private readonly Type $type
    ) {
        RectorAssert::className($class);
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getType(): Type
    {
        return $this->type;
    }
}
