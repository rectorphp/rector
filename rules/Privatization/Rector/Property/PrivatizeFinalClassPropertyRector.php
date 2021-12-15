<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector\PrivatizeFinalClassPropertyRectorTest
 */
final class PrivatizeFinalClassPropertyRector extends AbstractRector
{
    public function __construct(
        private readonly VisibilityManipulator $visibilityManipulator,
        private readonly AstResolver $astResolver
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change property to private if possible', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    protected $value;
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    private $value;
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        $classLike = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (! $classLike instanceof Class_) {
            return null;
        }

        if (! $classLike->isFinal()) {
            return null;
        }

        if ($this->shouldSkipProperty($node)) {
            return null;
        }

        if ($classLike->extends === null) {
            $this->visibilityManipulator->makePrivate($node);
            return $node;
        }

        if ($this->isPropertyVisibilityGuardedByParent($node, $classLike)) {
            return null;
        }

        $this->visibilityManipulator->makePrivate($node);

        return $node;
    }

    private function shouldSkipProperty(Property $property): bool
    {
        if (count($property->props) !== 1) {
            return true;
        }

        return ! $property->isProtected();
    }

    private function isPropertyVisibilityGuardedByParent(Property $property, Class_ $class): bool
    {
        if ($class->extends === null) {
            return false;
        }

        /** @var Scope $scope */
        $scope = $property->getAttribute(AttributeKey::SCOPE);

        /** @var ClassReflection $classReflection */
        $classReflection = $scope->getClassReflection();

        $propertyName = $this->getName($property);

        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasProperty($propertyName)) {
                return true;
            }

            if ($this->isFoundInParentClassMethods($parentClassReflection, $propertyName)) {
                return true;
            }
        }

        return false;
    }

    private function isFoundInParentClassMethods(ClassReflection $parentClassReflection, string $propertyName): bool
    {
        $classLike = $this->astResolver->resolveClassFromName($parentClassReflection->getName());
        if (! $classLike instanceof ClassLike) {
            return false;
        }

        $methods = $classLike->getMethods();
        foreach ($methods as $method) {
            $isFound = (bool) $this->betterNodeFinder->findFirst((array) $method->stmts, function (Node $subNode) use (
                $propertyName
            ): bool {
                if (! $subNode instanceof PropertyFetch) {
                    return false;
                }

                if (! $subNode->var instanceof Variable) {
                    return false;
                }

                if (! $this->nodeNameResolver->isName($subNode->var, 'this')) {
                    return false;
                }

                return $this->nodeNameResolver->isName($subNode, $propertyName);
            });

            if ($isFound) {
                return true;
            }
        }

        return false;
    }
}
