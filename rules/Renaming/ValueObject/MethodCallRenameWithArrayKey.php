<?php

declare (strict_types=1);
namespace Rector\Renaming\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Renaming\Contract\MethodCallRenameInterface;
final class MethodCallRenameWithArrayKey implements \Rector\Renaming\Contract\MethodCallRenameInterface
{
    /**
     * @var string
     */
    private $oldClass;
    /**
     * @var string
     */
    private $oldMethod;
    /**
     * @var string
     */
    private $newMethod;
    private $arrayKey;
    /**
     * @param mixed $arrayKey
     */
    public function __construct(string $oldClass, string $oldMethod, string $newMethod, $arrayKey)
    {
        $this->oldClass = $oldClass;
        $this->oldMethod = $oldMethod;
        $this->newMethod = $newMethod;
        $this->arrayKey = $arrayKey;
    }
    public function getOldObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->oldClass);
    }
    public function getOldMethod() : string
    {
        return $this->oldMethod;
    }
    public function getNewMethod() : string
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
