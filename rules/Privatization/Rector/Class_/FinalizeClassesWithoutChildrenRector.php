<?php

declare (strict_types=1);
namespace Rector\Privatization\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\NodeAnalyzer\DoctrineEntityAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector\FinalizeClassesWithoutChildrenRectorTest
 */
final class FinalizeClassesWithoutChildrenRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;
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
    public function __construct(ClassAnalyzer $classAnalyzer, FamilyRelationsAnalyzer $familyRelationsAnalyzer, VisibilityManipulator $visibilityManipulator, ReflectionResolver $reflectionResolver, DoctrineEntityAnalyzer $doctrineEntityAnalyzer)
    {
        $this->classAnalyzer = $classAnalyzer;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
        $this->visibilityManipulator = $visibilityManipulator;
        $this->reflectionResolver = $reflectionResolver;
        $this->doctrineEntityAnalyzer = $doctrineEntityAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Finalize every class that has no children', [new CodeSample(<<<'CODE_SAMPLE'
class FirstClass
{
}

class SecondClass
{
}

class ThirdClass extends SecondClass
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class FirstClass
{
}

class SecondClass
{
}

final class ThirdClass extends SecondClass
{
}
CODE_SAMPLE
)]);
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
        $childrenClassReflections = $this->familyRelationsAnalyzer->getChildrenOfClassReflection($classReflection);
        if ($childrenClassReflections !== []) {
            return null;
        }
        if ($node->attrGroups !== []) {
            // improve reprint with correct newline
            $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }
        $this->visibilityManipulator->makeFinal($node);
        return $node;
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        if ($class->isFinal()) {
            return \true;
        }
        if ($class->isAbstract()) {
            return \true;
        }
        return $this->classAnalyzer->isAnonymousClass($class);
    }
}
