<?php

declare(strict_types=1);

namespace Rector\Renaming\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Renaming\Contract\MethodCallRenameInterface;

final class MethodCallRenameWithArrayKey implements MethodCallRenameInterface
{
    /**
     * @param mixed $arrayKey
     */
    public function __construct(
        private string $oldClass,
        private string $oldMethod,
        private string $newMethod,
        private $arrayKey
    ) {
    }

    public function getOldObjectType(): ObjectType
    {
        return new ObjectType($this->oldClass);
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
