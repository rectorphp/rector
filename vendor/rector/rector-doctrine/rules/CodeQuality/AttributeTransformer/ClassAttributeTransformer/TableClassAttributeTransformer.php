<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AttributeTransformer\ClassAttributeTransformer;

use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use Rector\Doctrine\CodeQuality\Contract\ClassAttributeTransformerInterface;
use Rector\Doctrine\CodeQuality\NodeFactory\AttributeFactory;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Rector\Doctrine\Enum\MappingClass;
use Rector\PhpParser\Node\NodeFactory;
final class TableClassAttributeTransformer implements ClassAttributeTransformerInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @var string
     */
    private const TABLE_KEY = 'table';
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    public function transform(EntityMapping $entityMapping, Class_ $class) : void
    {
        $classMapping = $entityMapping->getClassMapping();
        $table = $classMapping[self::TABLE_KEY] ?? null;
        if (isset($classMapping['type']) && $classMapping['type'] !== 'entity') {
            return;
        }
        $args = [];
        if (\is_string($table)) {
            $args[] = AttributeFactory::createNamedArg(new String_($table), 'name');
        }
        $class->attrGroups[] = AttributeFactory::createGroup($this->getClassName(), $args);
        $uniqueConstraints = $classMapping['uniqueConstraints'] ?? [];
        foreach ($uniqueConstraints as $name => $uniqueConstraint) {
            $uniqueConstraint = \array_merge(['name' => $name], $uniqueConstraint);
            $args = $this->nodeFactory->createArgs($uniqueConstraint);
            $class->attrGroups[] = AttributeFactory::createGroup(MappingClass::UNIQUE_CONSTRAINT, $args);
        }
    }
    public function getClassName() : string
    {
        return MappingClass::TABLE;
    }
}
