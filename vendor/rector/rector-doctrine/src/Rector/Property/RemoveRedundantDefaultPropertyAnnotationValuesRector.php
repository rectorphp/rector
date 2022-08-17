<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\NodeAnalyzer\AttributeCleaner;
use Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Rector\Doctrine\NodeManipulator\DoctrineItemDefaultValueManipulator;
use Rector\Doctrine\ValueObject\ArgName;
use Rector\Doctrine\ValueObject\DefaultAnnotationArgValue;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
        $hasPhpDocInfoChanged = $this->clearPropertyAnnotations($node);
        $hasAttributeChanged = \false;
        foreach ($this->defaultAnnotationArgValues as $defaultAnnotationArgValue) {
            $argExpr = $this->attributeFinder->findAttributeByClassArgByName($node, $defaultAnnotationArgValue->getAnnotationClass(), $defaultAnnotationArgValue->getArgName());
            if (!$argExpr instanceof Expr) {
                continue;
            }
            if (!$this->valueResolver->isValue($argExpr, $defaultAnnotationArgValue->getDefaultValue())) {
                continue;
            }
            $this->attributeCleaner->clearAttributeAndArgName($node, $defaultAnnotationArgValue->getAnnotationClass(), $defaultAnnotationArgValue->getArgName());
            $hasAttributeChanged = \true;
        }
        if ($hasPhpDocInfoChanged || $hasAttributeChanged) {
            return $node;
        }
        return null;
    }
    private function clearPropertyAnnotations(Property $property) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return \false;
        }
        $hasPropertyChanged = \false;
        foreach ($this->defaultAnnotationArgValues as $defaultAnnotationArgValue) {
            $hasAnnotationChanged = $this->clearAnnotation($phpDocInfo, $defaultAnnotationArgValue);
            if ($hasAnnotationChanged) {
                $hasPropertyChanged = \true;
            }
        }
        return $hasPropertyChanged;
    }
    private function clearAnnotation(PhpDocInfo $phpDocInfo, DefaultAnnotationArgValue $defaultAnnotationArgValue) : bool
    {
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass($defaultAnnotationArgValue->getAnnotationClass());
        if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return \false;
        }
        return $this->doctrineItemDefaultValueManipulator->clearDoctrineAnnotationTagValueNode($phpDocInfo, $doctrineAnnotationTagValueNode, $defaultAnnotationArgValue->getArgName(), $defaultAnnotationArgValue->getDefaultValue());
    }
}
