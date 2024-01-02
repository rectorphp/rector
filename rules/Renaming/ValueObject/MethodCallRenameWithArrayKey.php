<?php

declare (strict_types=1);
namespace Rector\Renaming\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Renaming\Contract\MethodCallRenameInterface;
use Rector\Validation\RectorAssert;
final class MethodCallRenameWithArrayKey implements MethodCallRenameInterface
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
     * @readonly
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
        RectorAssert::className($class);
        RectorAssert::methodName($oldMethod);
        RectorAssert::methodName($newMethod);
    }
    public function getClass() : string
    {
        return $this->class;
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->class);
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
