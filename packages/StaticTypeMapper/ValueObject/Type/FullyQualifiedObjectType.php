<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\ValueObject\Type;

use RectorPrefix202301\Nette\Utils\Strings;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @api
 */
final class FullyQualifiedObjectType extends ObjectType
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
        return (string) Strings::after($className, '\\', -1);
    }
    public function getShortNameNode() : Name
    {
        $name = new Name($this->getShortName());
        // to avoid processing short name twice
        $name->setAttribute(AttributeKey::VIRTUAL_NODE, \true);
        // keep original to avoid loss on while importing
        $name->setAttribute(AttributeKey::NAMESPACED_NAME, $this->getClassName());
        return $name;
    }
    public function getUseNode() : Use_
    {
        $name = new Name($this->getClassName());
        $useUse = new UseUse($name);
        $name->setAttribute(AttributeKey::PARENT_NODE, $useUse);
        return new Use_([$useUse]);
    }
    public function getFunctionUseNode() : Use_
    {
        $name = new Name($this->getClassName());
        $useUse = new UseUse($name, null);
        $name->setAttribute(AttributeKey::PARENT_NODE, $useUse);
        $use = new Use_([$useUse]);
        $use->type = Use_::TYPE_FUNCTION;
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
