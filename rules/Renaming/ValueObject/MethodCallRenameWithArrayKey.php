<?php

declare(strict_types=1);

namespace Rector\Renaming\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
use Rector\Renaming\Contract\MethodCallRenameInterface;

final class MethodCallRenameWithArrayKey implements MethodCallRenameInterface
{
    /**
     * @param mixed $arrayKey
     */
    public function __construct(
        private readonly string $class,
        private readonly string $oldMethod,
        private readonly string $newMethod,
        private        $arrayKey
    ) {
        RectorAssert::className($class);
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->class);
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
     * @return mixed
     */
    public function getArrayKey()
    {
        return $this->arrayKey;
    }
}
