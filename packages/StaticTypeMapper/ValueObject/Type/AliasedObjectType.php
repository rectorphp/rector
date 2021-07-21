<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\ValueObject\Type;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class AliasedObjectType extends \PHPStan\Type\ObjectType
{
    /**
     * @var string
     */
    private $fullyQualifiedClass;
    public function __construct(string $alias, string $fullyQualifiedClass)
    {
        $this->fullyQualifiedClass = $fullyQualifiedClass;
        parent::__construct($alias);
    }
    public function getFullyQualifiedClass() : string
    {
        return $this->fullyQualifiedClass;
    }
    public function getUseNode() : \PhpParser\Node\Stmt\Use_
    {
        $name = new \PhpParser\Node\Name($this->fullyQualifiedClass);
        $useUse = new \PhpParser\Node\Stmt\UseUse($name, $this->getClassName());
        return new \PhpParser\Node\Stmt\Use_([$useUse]);
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
    public function getFunctionUseNode() : \PhpParser\Node\Stmt\Use_
    {
        $name = new \PhpParser\Node\Name($this->fullyQualifiedClass);
        $useUse = new \PhpParser\Node\Stmt\UseUse($name, $this->getClassName());
        $name->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE, $useUse);
        $use = new \PhpParser\Node\Stmt\Use_([$useUse]);
        $use->type = \PhpParser\Node\Stmt\Use_::TYPE_FUNCTION;
        return $use;
    }
}
