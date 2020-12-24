<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\ValueObject\Type;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class AliasedObjectType extends ObjectType
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

    public function getFullyQualifiedClass(): string
    {
        return $this->fullyQualifiedClass;
    }

    public function getUseNode(): Use_
    {
        $name = new Name($this->fullyQualifiedClass);
        $useUse = new UseUse($name, $this->getClassName());

        $name->setAttribute(AttributeKey::PARENT_NODE, $useUse);

        return new Use_([$useUse]);
    }

    public function getShortName(): string
    {
        return $this->getClassName();
    }

    /**
     * @param AliasedObjectType|FullyQualifiedObjectType $comparedObjectType
     */
    public function areShortNamesEqual(ObjectType $comparedObjectType): bool
    {
        return $this->getShortName() === $comparedObjectType->getShortName();
    }
}
