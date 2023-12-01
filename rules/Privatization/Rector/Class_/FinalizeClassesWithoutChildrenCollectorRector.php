<?php

declare (strict_types=1);
namespace Rector\Privatization\Rector\Class_;

use RectorPrefix202312\Nette\Utils\Arrays;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Collector\ParentClassCollector;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\NodeAnalyzer\DoctrineEntityAnalyzer;
use Rector\Core\Rector\AbstractCollectorRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenCollectorRector\FinalizeClassesWithoutChildrenCollectorRectorTest
 */
final class FinalizeClassesWithoutChildrenCollectorRector extends AbstractCollectorRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\DoctrineEntityAnalyzer
     */
    private $doctrineEntityAnalyzer;
    public function __construct(ClassAnalyzer $classAnalyzer, VisibilityManipulator $visibilityManipulator, ReflectionResolver $reflectionResolver, DoctrineEntityAnalyzer $doctrineEntityAnalyzer)
    {
        $this->classAnalyzer = $classAnalyzer;
        $this->visibilityManipulator = $visibilityManipulator;
        $this->reflectionResolver = $reflectionResolver;
        $this->doctrineEntityAnalyzer = $doctrineEntityAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        if ($this->doctrineEntityAnalyzer->hasClassAnnotation($node)) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if ($this->doctrineEntityAnalyzer->hasClassReflectionAttribute($classReflection)) {
            return null;
        }
        $parentClassNames = $this->resolveCollectedParentClassNames($this->getCollectedDataNode());
        // the class is being extended in the code, so we should skip it here
        if ($this->nodeNameResolver->isNames($node, $parentClassNames)) {
            return null;
        }
        if ($node->attrGroups !== []) {
            // improve reprint with correct newline
            $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }
        $this->visibilityManipulator->makeFinal($node);
        return $node;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('...', []);
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        if ($class->isFinal() || $class->isAbstract()) {
            return \true;
        }
        return $this->classAnalyzer->isAnonymousClass($class);
    }
    /**
     * @return string[]
     */
    private function resolveCollectedParentClassNames(CollectedDataNode $collectedDataNode) : array
    {
        $parentClassCollectorData = $collectedDataNode->get(ParentClassCollector::class);
        $parentClassNames = Arrays::flatten($parentClassCollectorData);
        return \array_unique($parentClassNames);
    }
}
