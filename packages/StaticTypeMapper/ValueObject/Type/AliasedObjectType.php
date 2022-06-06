<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type;

use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Stmt\Use_;
use RectorPrefix20220606\PhpParser\Node\Stmt\UseUse;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\TypeWithClassName;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
final class AliasedObjectType extends ObjectType
{
    /**
     * @readonly
     * @var string
     */
    private $fullyQualifiedClass;
    public function __construct(string $alias, string $fullyQualifiedClass)
    {
        $this->fullyQualifiedClass = $fullyQualifiedClass;
        parent::__construct($alias);
    }
    public function getFullyQualifiedName() : string
    {
        return $this->fullyQualifiedClass;
    }
    public function getUseNode() : Use_
    {
        $name = new Name($this->fullyQualifiedClass);
        $useUse = new UseUse($name, $this->getClassName());
        return new Use_([$useUse]);
    }
    public function getShortName() : string
    {
        return $this->getClassName();
    }
    /**
     * @param $this|\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $comparedObjectType
     */
    public function areShortNamesEqual($comparedObjectType) : bool
    {
        return $this->getShortName() === $comparedObjectType->getShortName();
    }
    public function getFunctionUseNode() : Use_
    {
        $name = new Name($this->fullyQualifiedClass);
        $useUse = new UseUse($name, $this->getClassName());
        $name->setAttribute(AttributeKey::PARENT_NODE, $useUse);
        $use = new Use_([$useUse]);
        $use->type = Use_::TYPE_FUNCTION;
        return $use;
    }
    public function equals(Type $type) : bool
    {
        // compare with FQN classes
        if ($type instanceof TypeWithClassName) {
            if ($type instanceof self && $this->fullyQualifiedClass === $type->getFullyQualifiedName()) {
                return \true;
            }
            if ($this->fullyQualifiedClass === $type->getClassName()) {
                return \true;
            }
        }
        return parent::equals($type);
    }
}
