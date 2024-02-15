<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\AttributeTransformer\ClassAttributeTransformer;

use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use Rector\Doctrine\CodeQuality\Contract\ClassAttributeTransformerInterface;
use Rector\Doctrine\CodeQuality\NodeFactory\AttributeFactory;
use Rector\Doctrine\CodeQuality\Utils\CaseStringHelper;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Rector\Doctrine\Enum\MappingClass;
use Rector\PhpParser\Node\NodeFactory;
final class SoftDeletableClassAttributeTransformer implements ClassAttributeTransformerInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    public function transform(EntityMapping $entityMapping, Class_ $class) : void
    {
        $classMapping = $entityMapping->getClassMapping();
        $softDeletableMapping = $classMapping['gedmo']['soft_deleteable'] ?? null;
        if (!\is_array($softDeletableMapping)) {
            return;
        }
        $args = $this->nodeFactory->createArgs($softDeletableMapping);
        foreach ($args as $arg) {
            if (!$arg->name instanceof Identifier) {
                continue;
            }
            $arg->name = new Identifier(CaseStringHelper::camelCase($arg->name->toString()));
        }
        $class->attrGroups[] = AttributeFactory::createGroup($this->getClassName(), $args);
    }
    public function getClassName() : string
    {
        return MappingClass::GEDMO_SOFT_DELETEABLE;
    }
}
