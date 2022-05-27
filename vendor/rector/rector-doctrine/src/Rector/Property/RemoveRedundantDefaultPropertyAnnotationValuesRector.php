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
final class RemoveRedundantDefaultPropertyAnnotationValuesRector extends \Rector\Core\Rector\AbstractRector
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
    public function __construct(\Rector\Doctrine\NodeManipulator\DoctrineItemDefaultValueManipulator $doctrineItemDefaultValueManipulator, \Rector\Doctrine\NodeAnalyzer\AttributeFinder $attributeFinder, \Rector\Doctrine\NodeAnalyzer\AttributeCleaner $attributeCleaner)
    {
        $this->doctrineItemDefaultValueManipulator = $doctrineItemDefaultValueManipulator;
        $this->attributeFinder = $attributeFinder;
        $this->attributeCleaner = $attributeCleaner;
        $this->defaultAnnotationArgValues = [new \Rector\Doctrine\ValueObject\DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\Column', 'nullable', \false), new \Rector\Doctrine\ValueObject\DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\Column', 'unique', \false), new \Rector\Doctrine\ValueObject\DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\Column', 'precision', 0), new \Rector\Doctrine\ValueObject\DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\Column', 'scale', 0), new \Rector\Doctrine\ValueObject\DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\GeneratedValue', 'strategy', 'AUTO'), new \Rector\Doctrine\ValueObject\DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\JoinColumn', 'unique', \false), new \Rector\Doctrine\ValueObject\DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\JoinColumn', 'nullable', \true), new \Rector\Doctrine\ValueObject\DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\JoinColumn', 'referencedColumnName', 'id'), new \Rector\Doctrine\ValueObject\DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\ManyToMany', \Rector\Doctrine\ValueObject\ArgName::ORPHAN_REMOVAL, \false), new \Rector\Doctrine\ValueObject\DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\ManyToMany', \Rector\Doctrine\ValueObject\ArgName::FETCH, \Rector\Doctrine\ValueObject\ArgName::LAZY), new \Rector\Doctrine\ValueObject\DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\ManyToOne', \Rector\Doctrine\ValueObject\ArgName::FETCH, \Rector\Doctrine\ValueObject\ArgName::LAZY), new \Rector\Doctrine\ValueObject\DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\OneToMany', \Rector\Doctrine\ValueObject\ArgName::ORPHAN_REMOVAL, \false), new \Rector\Doctrine\ValueObject\DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\OneToMany', \Rector\Doctrine\ValueObject\ArgName::FETCH, \Rector\Doctrine\ValueObject\ArgName::LAZY), new \Rector\Doctrine\ValueObject\DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\OneToOne', \Rector\Doctrine\ValueObject\ArgName::ORPHAN_REMOVAL, \false), new \Rector\Doctrine\ValueObject\DefaultAnnotationArgValue('Doctrine\\ORM\\Mapping\\OneToOne', \Rector\Doctrine\ValueObject\ArgName::FETCH, \Rector\Doctrine\ValueObject\ArgName::LAZY)];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Removes redundant default values from Doctrine ORM annotations/attributes properties', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $this->refactorPropertyAnnotations($node);
        foreach ($this->defaultAnnotationArgValues as $defaultAnnotationArgValue) {
            $argExpr = $this->attributeFinder->findAttributeByClassArgByName($node, $defaultAnnotationArgValue->getAnnotationClass(), $defaultAnnotationArgValue->getArgName());
            if (!$argExpr instanceof \PhpParser\Node\Expr) {
                continue;
            }
            if (!$this->valueResolver->isValue($argExpr, $defaultAnnotationArgValue->getDefaultValue())) {
                continue;
            }
            $this->attributeCleaner->clearAttributeAndArgName($node, $defaultAnnotationArgValue->getAnnotationClass(), $defaultAnnotationArgValue->getArgName());
        }
        return $node;
    }
    private function refactorPropertyAnnotations(\PhpParser\Node\Stmt\Property $property) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
        if ($phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            foreach ($this->defaultAnnotationArgValues as $defaultAnnotationArgValue) {
                $this->refactorAnnotation($phpDocInfo, $defaultAnnotationArgValue->getAnnotationClass(), $defaultAnnotationArgValue->getArgName(), $defaultAnnotationArgValue->getDefaultValue());
            }
        }
    }
    /**
     * @param class-string $annotationClass
     * @param string|bool|int $defaultValue
     */
    private function refactorAnnotation(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, string $annotationClass, string $argName, $defaultValue) : void
    {
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass($annotationClass);
        if (!$doctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return;
        }
        $this->doctrineItemDefaultValueManipulator->remove($phpDocInfo, $doctrineAnnotationTagValueNode, $argName, $defaultValue);
    }
}
