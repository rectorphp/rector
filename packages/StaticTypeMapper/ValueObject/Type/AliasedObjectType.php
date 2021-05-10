<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\ValueObject\Type;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Type\ObjectType;
final class AliasedObjectType extends \PHPStan\Type\ObjectType
{
    /**
     * @var string
     */
    private $fullyQualifiedClass;
    public function __construct(string $alias, string $fullyQualifiedClass)
    {
        parent::__construct($alias);
        $this->fullyQualifiedClass = $fullyQualifiedClass;
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
     * @param AliasedObjectType|FullyQualifiedObjectType $comparedObjectType
     */
    public function areShortNamesEqual(\PHPStan\Type\ObjectType $comparedObjectType) : bool
    {
        return $this->getShortName() === $comparedObjectType->getShortName();
    }
}
