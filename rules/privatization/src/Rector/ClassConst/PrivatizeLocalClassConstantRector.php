<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\ApiPhpDocTagNode;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\NodeFinder\ParentClassConstantNodeFinder;
use Rector\Privatization\Reflection\ParentConstantReflectionResolver;
use Rector\Privatization\ValueObject\ConstantVisibility;
use ReflectionClassConstant;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\PrivatizeLocalClassConstantRectorTest
 */
final class PrivatizeLocalClassConstantRector extends AbstractRector implements ZeroCacheRectorInterface
{
    /**
     * @var string
     */
    private const HAS_NEW_ACCESS_LEVEL = 'has_new_access_level';

    /**
     * @var ParentConstantReflectionResolver
     */
    private $parentConstantReflectionResolver;

    /**
     * @var ParentClassConstantNodeFinder
     */
    private $parentClassConstantNodeFinder;

    public function __construct(
        ParentClassConstantNodeFinder $parentClassConstantNodeFinder,
        ParentConstantReflectionResolver $parentConstantReflectionResolver
    ) {
        $this->parentConstantReflectionResolver = $parentConstantReflectionResolver;
        $this->parentClassConstantNodeFinder = $parentClassConstantNodeFinder;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Finalize every class constant that is used only locally',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class ClassWithConstantUsedOnlyHere
{
    const LOCAL_ONLY = true;

    public function isLocalOnly()
    {
        return self::LOCAL_ONLY;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class ClassWithConstantUsedOnlyHere
{
    private const LOCAL_ONLY = true;

    public function isLocalOnly()
    {
        return self::LOCAL_ONLY;
    }
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
        return [ClassConst::class];
    }

    /**
     * @param ClassConst $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var string $class */
        $class = $node->getAttribute(AttributeKey::CLASS_NAME);

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            throw new ShouldNotHappenException();
        }

        // Remember when we have already processed this constant recursively
        $node->setAttribute(self::HAS_NEW_ACCESS_LEVEL, true);

        $interface = $this->nodeRepository->findInterface($class);

        // 0. constants declared in interfaces have to be public
        if ($interface instanceof Interface_) {
            $this->visibilityManipulator->makePublic($node);
            return $node;
        }

        /** @var string $constant */
        $constant = $this->getName($node);

        $parentClassConstantVisibility = $this->findParentClassConstantAndRefactorIfPossible($class, $constant);

        // The parent's constant is public, so this one must become public too
        if ($parentClassConstantVisibility !== null && $parentClassConstantVisibility->isPublic()) {
            $this->visibilityManipulator->makePublic($node);
            return $node;
        }

        $directUsingClassReflections = $this->nodeRepository->findDirectClassConstantFetches(
            $classReflection,
            $constant
        );
        $indirectUsingClassReflections = $this->nodeRepository->findIndirectClassConstantFetches(
            $classReflection,
            $constant
        );

        $this->changeConstantVisibility(
            $node,
            $directUsingClassReflections,
            $indirectUsingClassReflections,
            $parentClassConstantVisibility,
            $classReflection
        );

        return $node;
    }

    private function shouldSkip(ClassConst $classConst): bool
    {
        $hasNewAccessLevel = $classConst->getAttribute(self::HAS_NEW_ACCESS_LEVEL);
        if ($hasNewAccessLevel) {
            return true;
        }

        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::CONSTANT_VISIBILITY)) {
            return true;
        }

        if (count($classConst->consts) !== 1) {
            return true;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classConst);
        if ($phpDocInfo->hasByType(ApiPhpDocTagNode::class)) {
            return true;
        }

        /** @var string|null $class */
        $class = $classConst->getAttribute(AttributeKey::CLASS_NAME);
        return $class === null;
    }

    private function findParentClassConstantAndRefactorIfPossible(string $class, string $constant): ?ConstantVisibility
    {
        $parentClassConst = $this->parentClassConstantNodeFinder->find($class, $constant);

        if ($parentClassConst !== null) {
            // Make sure the parent's constant has been refactored
            $this->refactor($parentClassConst);

            return new ConstantVisibility(
                $parentClassConst->isPublic(),
                $parentClassConst->isProtected(),
                $parentClassConst->isPrivate()
            );
            // If the constant isn't declared in the parent, it might be declared in the parent's parent
        }

        $reflectionClassConstant = $this->parentConstantReflectionResolver->resolve($class, $constant);
        if (! $reflectionClassConstant instanceof ReflectionClassConstant) {
            return null;
        }

        return new ConstantVisibility(
            $reflectionClassConstant->isPublic(),
            $reflectionClassConstant->isProtected(),
            $reflectionClassConstant->isPrivate()
        );
    }

    /**
     * @param ClassReflection[] $directUsingClassReflections
     * @param ClassReflection[] $indirectUsingClassReflections
     */
    private function changeConstantVisibility(
        ClassConst $classConst,
        array $directUsingClassReflections,
        array $indirectUsingClassReflections,
        ?ConstantVisibility $constantVisibility,
        ClassReflection $classReflection
    ): void {
        // 1. is actually never used
        if ($directUsingClassReflections === []) {
            if ($indirectUsingClassReflections !== [] && $constantVisibility !== null) {
                $this->visibilityManipulator->makeClassConstPrivateOrWeaker($classConst, $constantVisibility);
            }

            return;
        }

        // 2. is only local use? → private
        if ($directUsingClassReflections === [$classReflection]) {
            if ($indirectUsingClassReflections === []) {
                $this->visibilityManipulator->makeClassConstPrivateOrWeaker($classConst, $constantVisibility);
            }

            return;
        }

        $usingClassReflections = array_merge($indirectUsingClassReflections, $directUsingClassReflections);

        // 3. used by children → protected
        if ($this->isUsedByChildrenOnly($usingClassReflections, $classReflection)) {
            $this->visibilityManipulator->makeProtected($classConst);
        } else {
            $this->visibilityManipulator->makePublic($classConst);
        }
    }

    /**
     * @param ClassReflection[] $constantUsingClassReflections
     */
    private function isUsedByChildrenOnly(array $constantUsingClassReflections, ClassReflection $classReflection): bool
    {
        $isChild = false;

        foreach ($constantUsingClassReflections as $constantUsingClassReflection) {
            if ($constantUsingClassReflection->isSubclassOf($classReflection->getName())) {
                $isChild = true;
            } else {
                // not a child, must be public
                return false;
            }
        }

        return $isChild;
    }
}
