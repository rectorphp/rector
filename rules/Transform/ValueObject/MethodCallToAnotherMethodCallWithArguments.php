<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;

final class MethodCallToAnotherMethodCallWithArguments
{
    /**
     * @param mixed[] $newArguments
     */
    public function __construct(
        private string $type,
        private string $oldMethod,
        private string $newMethod,
        private array $newArguments
    ) {
        RectorAssert::className($type);
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->type);
    }

    public function getOldMethod(): string
    {
        return $this->oldMethod;
    }

    public function getNewMethod(): string
    {
        return $this->newMethod;
    }

    /**
     * @return mixed[]
     */
    public function getNewArguments(): array
    {
        return $this->newArguments;
    }
}
