<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\ValueObject\Type;

use RectorPrefix20210514\Nette\Utils\Strings;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class FullyQualifiedObjectType extends \PHPStan\Type\ObjectType
{
    public function getShortNameType() : \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType
    {
        return new \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType($this->getShortName(), $this->getClassName());
    }
    /**
     * @param AliasedObjectType|FullyQualifiedObjectType $comparedObjectType
     */
    public function areShortNamesEqual(\PHPStan\Type\ObjectType $comparedObjectType) : bool
    {
        return $this->getShortName() === $comparedObjectType->getShortName();
    }
    public function getShortName() : string
    {
        if (!\RectorPrefix20210514\Nette\Utils\Strings::contains($this->getClassName(), '\\')) {
            return $this->getClassName();
        }
        return (string) \RectorPrefix20210514\Nette\Utils\Strings::after($this->getClassName(), '\\', -1);
    }
    public function getShortNameNode() : \PhpParser\Node\Name
    {
        $name = new \PhpParser\Node\Name($this->getShortName());
        $name->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::VIRTUAL_NODE, \true);
        return $name;
    }
    public function getUseNode() : \PhpParser\Node\Stmt\Use_
    {
        $name = new \PhpParser\Node\Name($this->getClassName());
        $useUse = new \PhpParser\Node\Stmt\UseUse($name);
        $name->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE, $useUse);
        return new \PhpParser\Node\Stmt\Use_([$useUse]);
    }
    public function getFunctionUseNode() : \PhpParser\Node\Stmt\Use_
    {
        $name = new \PhpParser\Node\Name($this->getClassName());
        $useUse = new \PhpParser\Node\Stmt\UseUse($name, null, \PhpParser\Node\Stmt\Use_::TYPE_FUNCTION);
        $name->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE, $useUse);
        return new \PhpParser\Node\Stmt\Use_([$useUse]);
    }
    public function getShortNameLowered() : string
    {
        return \strtolower($this->getShortName());
    }
    public function getClassNameLowered() : string
    {
        return \strtolower($this->getClassName());
    }
}
