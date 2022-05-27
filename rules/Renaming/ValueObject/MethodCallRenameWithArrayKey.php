<?php

declare (strict_types=1);
namespace Rector\Renaming\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
use Rector\Renaming\Contract\MethodCallRenameInterface;
final class MethodCallRenameWithArrayKey implements \Rector\Renaming\Contract\MethodCallRenameInterface
{
    /**
     * @readonly
     * @var string
     */
    private $class;
    /**
     * @readonly
     * @var string
     */
    private $oldMethod;
    /**
     * @readonly
     * @var string
     */
    private $newMethod;
    /**
     * @var mixed
     */
    private $arrayKey;
    /**
     * @param mixed $arrayKey
     */
    public function __construct(string $class, string $oldMethod, string $newMethod, $arrayKey)
    {
        $this->class = $class;
        $this->oldMethod = $oldMethod;
        $this->newMethod = $newMethod;
        $this->arrayKey = $arrayKey;
        \Rector\Core\Validation\RectorAssert::className($class);
    }
    public function getClass() : string
    {
        return $this->class;
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->class);
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
