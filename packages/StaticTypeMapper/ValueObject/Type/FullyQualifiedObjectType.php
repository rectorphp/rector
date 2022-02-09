<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\ValueObject\Type;

use RectorPrefix20220209\Nette\Utils\Strings;
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
     * @param \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType|\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType $comparedObjectType
     */
    public function areShortNamesEqual($comparedObjectType) : bool
    {
        return $this->getShortName() === $comparedObjectType->getShortName();
    }
    public function getShortName() : string
    {
        $className = $this->getClassName();
        if (\strpos($className, '\\') === \false) {
            return $className;
        }
        return (string) \RectorPrefix20220209\Nette\Utils\Strings::after($className, '\\', -1);
    }
    public function getShortNameNode() : \PhpParser\Node\Name
    {
        $name = new \PhpParser\Node\Name($this->getShortName());
        // to avoid processing short name twice
        $name->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::VIRTUAL_NODE, \true);
        // keep original to avoid loss on while importing
        $name->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NAMESPACED_NAME, $this->getClassName());
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
        $useUse = new \PhpParser\Node\Stmt\UseUse($name, null);
        $name->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE, $useUse);
        $use = new \PhpParser\Node\Stmt\Use_([$useUse]);
        $use->type = \PhpParser\Node\Stmt\Use_::TYPE_FUNCTION;
        return $use;
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
