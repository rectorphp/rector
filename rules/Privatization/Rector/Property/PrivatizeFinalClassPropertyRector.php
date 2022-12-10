<?php

declare (strict_types=1);
namespace Rector\Privatization\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\TraitUse;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Privatization\Guard\ParentPropertyLookupGuard;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector\PrivatizeFinalClassPropertyRectorTest
 */
final class PrivatizeFinalClassPropertyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    /**
     * @readonly
     * @var \Rector\Privatization\Guard\ParentPropertyLookupGuard
     */
    private $parentPropertyLookupGuard;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(VisibilityManipulator $visibilityManipulator, ParentPropertyLookupGuard $parentPropertyLookupGuard, ReflectionProvider $reflectionProvider)
    {
        $this->visibilityManipulator = $visibilityManipulator;
        $this->parentPropertyLookupGuard = $parentPropertyLookupGuard;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change property to private if possible', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    protected $value;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private $value;
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
        if (!$node->isFinal()) {
            return null;
        }
        $traitPropertyNames = $this->resolveTraitPropertyNames($node);
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            if ($this->shouldSkipProperty($property, $traitPropertyNames)) {
                continue;
            }
            if (!$this->parentPropertyLookupGuard->isLegal($property)) {
                continue;
            }
            $this->visibilityManipulator->makePrivate($property);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @return string[]
     */
    private function resolveTraitPropertyNames(Class_ $class) : array
    {
        $traitPropertyNames = [];
        foreach ($class->stmts as $classStmt) {
            if (!$classStmt instanceof TraitUse) {
                continue;
            }
            foreach ($classStmt->traits as $trait) {
                $traitName = $this->getName($trait);
                if (!$this->reflectionProvider->hasClass($traitName)) {
                    continue;
                }
                $traitReflection = $this->reflectionProvider->getClass($traitName);
                $nativeTraitReflection = $traitReflection->getNativeReflection();
                foreach ($nativeTraitReflection->getProperties() as $propertyReflection) {
                    $traitPropertyNames[] = $propertyReflection->getName();
                }
            }
        }
        return $traitPropertyNames;
    }
    /**
     * @param string[] $traitPropertyNames
     */
    private function shouldSkipProperty(Property $property, array $traitPropertyNames) : bool
    {
        if (\count($property->props) !== 1) {
            return \true;
        }
        if (!$property->isProtected()) {
            return \true;
        }
        return $this->isNames($property, $traitPropertyNames);
    }
}
