<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;

final class PropertyFetchToMethodCall
{
    /**
     * @param mixed[] $newGetArguments
     */
    public function __construct(
        private readonly string $oldType,
        private readonly string $oldProperty,
        private readonly string $newGetMethod,
        private readonly ?string $newSetMethod = null,
        private readonly array $newGetArguments = []
    ) {
        RectorAssert::className($oldType);
    }

    public function getOldObjectType(): ObjectType
    {
        return new ObjectType($this->oldType);
    }

    public function getOldProperty(): string
    {
        return $this->oldProperty;
    }

    public function getNewGetMethod(): string
    {
        return $this->newGetMethod;
    }

    public function getNewSetMethod(): ?string
    {
        return $this->newSetMethod;
    }

    /**
     * @return mixed[]
     */
    public function getNewGetArguments(): array
    {
        return $this->newGetArguments;
    }
}
