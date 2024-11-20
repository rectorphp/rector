<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\ValueObject\Type;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\StaticTypeMapper\Resolver\ClassNameFromObjectTypeResolver;
/**
 * @api
 */
final class AliasedObjectType extends ObjectType
{
    /**
     * @readonly
     */
    private string $fullyQualifiedClass;
    public function __construct(string $alias, string $fullyQualifiedClass)
    {
        $this->fullyQualifiedClass = $fullyQualifiedClass;
        parent::__construct($alias);
    }
    public function getFullyQualifiedName() : string
    {
        return $this->fullyQualifiedClass;
    }
    /**
     * @param Use_::TYPE_* $useType
     */
    public function getUseNode(int $useType) : Use_
    {
        $name = new Name($this->fullyQualifiedClass);
        $useItem = new UseItem($name, $this->getClassName());
        $use = new Use_([$useItem]);
        $use->type = $useType;
        return $use;
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
    public function equals(Type $type) : bool
    {
        $className = ClassNameFromObjectTypeResolver::resolve($type);
        // compare with FQN classes
        if ($className !== null) {
            if ($type instanceof self && $this->fullyQualifiedClass === $type->getFullyQualifiedName()) {
                return \true;
            }
            if ($this->fullyQualifiedClass === $className) {
                return \true;
            }
        }
        return parent::equals($type);
    }
}
