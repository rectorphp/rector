<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Doctrine\Rector\Property;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Doctrine\NodeAnalyzer\AttributeCleaner;
use RectorPrefix20220606\Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use RectorPrefix20220606\Rector\Doctrine\NodeManipulator\DoctrineItemDefaultValueManipulator;
use RectorPrefix20220606\Rector\Doctrine\ValueObject\ArgName;
use RectorPrefix20220606\Rector\Doctrine\ValueObject\DefaultAnnotationArgValue;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\RemoveRedundantDefaultPropertyAnnotationValuesRectorTest
 *
 * @changelog https://www.doctrine-project.org/projects/doctrine-orm/en/2.8/reference/basic-mapping.html#property-mapping
 */
final class RemoveRedundantDefaultPropertyAnnotationValuesRector extends AbstractRector
{
    /**
     * @var DefaultAnnotationArgValue[]
     */
    private $defaultAnnotationArgValues = [];
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeManipulator\DoctrineItemDefaultValueManipulator
     */
    private $doctrineItemDefaultValueManipulator;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttributeFinder
     */
    private $attributeFinder;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttributeCleaner
     */
    private $attributeCleaner;
    public function __construct(DoctrineItemDefaultValueManipulator $doctrineItemDefaultValueManipulator, AttributeFinder $attributeFinder, AttributeCleaner $attributeCleaner)
    {
        $this->doctrineItemDefaultValueManipulator = $doctrineItemDefaultValueManipulator;
        $this->attributeFinder = $attributeFinder;
        $this->attributeCleaner = $attributeCleaner;
        $this->defaultAnnotationArgValues = [new DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\Column', 'nullable', \false), new DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\Column', 'unique', \false), new DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\Column', 'precision', 0), new DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\Column', 'scale', 0), new DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\GeneratedValue', 'strategy', 'AUTO'), new DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\JoinColumn', 'unique', \false), new DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\JoinColumn', 'nullable', \true), new DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\JoinColumn', 'referencedColumnName', 'id'), new DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\ManyToMany', ArgName::ORPHAN_REMOVAL, \false), new DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\ManyToMany', ArgName::FETCH, ArgName::LAZY), new DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\ManyToOne', ArgName::FETCH, ArgName::LAZY), new DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\OneToMany', ArgName::ORPHAN_REMOVAL, \false), new DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\OneToMany', ArgName::FETCH, ArgName::LAZY), new DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\OneToOne', ArgName::ORPHAN_REMOVAL, \false), new DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\OneToOne', ArgName::FETCH, ArgName::LAZY)];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Removes redundant default values from Doctrine ORM annotations/attributes properties', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\ManyToOne(targetEntity=Training::class)
     * @ORM\JoinColumn(name="training", unique=false)
     */
    private $training;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\ManyToOne(targetEntity=Training::class)
     * @ORM\JoinColumn(name="training")
     */
    private $training;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node) : ?Node
    {
        $this->refactorPropertyAnnotations($node);
        foreach ($this->defaultAnnotationArgValues as $defaultAnnotationArgValue) {
            $argExpr = $this->attributeFinder->findAttributeByClassArgByName($node, $defaultAnnotationArgValue->getAnnotationClass(), $defaultAnnotationArgValue->getArgName());
            if (!$argExpr instanceof Expr) {
                continue;
            }
            if (!$this->valueResolver->isValue($argExpr, $defaultAnnotationArgValue->getDefaultValue())) {
                continue;
            }
            $this->attributeCleaner->clearAttributeAndArgName($node, $defaultAnnotationArgValue->getAnnotationClass(), $defaultAnnotationArgValue->getArgName());
        }
        return $node;
    }
    private function refactorPropertyAnnotations(Property $property) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
        if ($phpDocInfo instanceof PhpDocInfo) {
            foreach ($this->defaultAnnotationArgValues as $defaultAnnotationArgValue) {
                $this->refactorAnnotation($phpDocInfo, $defaultAnnotationArgValue->getAnnotationClass(), $defaultAnnotationArgValue->getArgName(), $defaultAnnotationArgValue->getDefaultValue());
            }
        }
    }
    /**
     * @param class-string $annotationClass
     * @param string|bool|int $defaultValue
     */
    private function refactorAnnotation(PhpDocInfo $phpDocInfo, string $annotationClass, string $argName, $defaultValue) : void
    {
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass($annotationClass);
        if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return;
        }
        $this->doctrineItemDefaultValueManipulator->remove($phpDocInfo, $doctrineAnnotationTagValueNode, $argName, $defaultValue);
    }
}
