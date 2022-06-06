<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
final class MethodCallToAnotherMethodCallWithArguments
{
    /**
     * @readonly
     * @var string
     */
    private $type;
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
     * @var mixed[]
     * @readonly
     */
    private $newArguments;
    /**
     * @param mixed[] $newArguments
     */
    public function __construct(string $type, string $oldMethod, string $newMethod, array $newArguments)
    {
        $this->type = $type;
        $this->oldMethod = $oldMethod;
        $this->newMethod = $newMethod;
        $this->newArguments = $newArguments;
        \Rector\Core\Validation\RectorAssert::className($type);
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->type);
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
     * @return mixed[]
     */
    public function getNewArguments() : array
    {
        return $this->newArguments;
    }
}
