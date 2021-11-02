<?php

declare (strict_types=1);
namespace Rector\Renaming\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Renaming\Contract\MethodCallRenameInterface;
final class MethodCallRename implements \Rector\Renaming\Contract\MethodCallRenameInterface
{
    /**
     * @var string
     */
    private $class;
    /**
     * @var string
     */
    private $oldMethod;
    /**
     * @var string
     */
    private $newMethod;
    public function __construct(string $class, string $oldMethod, string $newMethod)
    {
        $this->class = $class;
        $this->oldMethod = $oldMethod;
        $this->newMethod = $newMethod;
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
}
