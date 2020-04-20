<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeCollector\NodeFinder\ClassConstParsedNodesFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\SOLID\NodeFinder\ParentClassConstantNodeFinder;
use Rector\SOLID\Reflection\ParentConstantReflectionResolver;
use Rector\SOLID\ValueObject\ConstantVisibility;

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

    /**
     * @var ClassConstParsedNodesFinder
     */
    private $classConstParsedNodesFinder;

    public function __construct(
        ParentConstantReflectionResolver $parentConstantReflectionResolver,
        ParentClassConstantNodeFinder $parentClassConstantNodeFinder,
        ClassConstParsedNodesFinder $classConstParsedNodesFinder
    ) {
        $this->parentConstantReflectionResolver = $parentConstantReflectionResolver;
        $this->parentClassConstantNodeFinder = $parentClassConstantNodeFinder;
        $this->classConstParsedNodesFinder = $classConstParsedNodesFinder;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Finalize every class constant that is used only locally', [
            new CodeSample(
                <<<'PHP'
class ClassWithConstantUsedOnlyHere
{
    const LOCAL_ONLY = true;

    public function isLocalOnly()
    {
        return self::LOCAL_ONLY;
    }
}
PHP
                ,
                <<<'PHP'
class ClassWithConstantUsedOnlyHere
{
    private const LOCAL_ONLY = true;

    public function isLocalOnly()
    {
        return self::LOCAL_ONLY;
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
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

        // Remember when we have already processed this constant recursively
        $node->setAttribute(self::HAS_NEW_ACCESS_LEVEL, true);

        /** @var string $class */
        $class = $node->getAttribute(AttributeKey::CLASS_NAME);

        // 0. constants declared in interfaces have to be public
        if ($this->classLikeParsedNodesFinder->findInterface($class) !== null) {
            $this->makePublic($node);
            return $node;
        }

        /** @var string $constant */
        $constant = $this->getName($node);

        $parentClassConstantVisibility = $this->findParentClassConstantAndRefactorIfPossible($class, $constant);

        // The parent's constant is public, so this one must become public too
        if ($parentClassConstantVisibility !== null && $parentClassConstantVisibility->isPublic()) {
            $this->makePublic($node);
            return $node;
        }

        $directUseClasses = $this->classConstParsedNodesFinder->findDirectClassConstantFetches($class, $constant);
        $indirectUseClasses = $this->classConstParsedNodesFinder->findIndirectClassConstantFetches($class, $constant);

        $this->changeConstantVisibility(
            $node,
            $directUseClasses,
            $indirectUseClasses,
            $parentClassConstantVisibility,
            $class
        );

        return $node;
    }

    private function shouldSkip(ClassConst $classConst): bool
    {
        if ($classConst->getAttribute(self::HAS_NEW_ACCESS_LEVEL)) {
            return true;
        }

        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::CONSTANT_VISIBILITY)) {
            return true;
        }

        return count($classConst->consts) !== 1;
    }

    private function findParentClassConstantAndRefactorIfPossible(string $class, string $constant): ?ConstantVisibility
    {
        $parentClassConstant = $this->parentClassConstantNodeFinder->find($class, $constant);

        if ($parentClassConstant !== null) {
            // Make sure the parent's constant has been refactored
            $this->refactor($parentClassConstant);

            return new ConstantVisibility(
                $parentClassConstant->isPublic(),
                $parentClassConstant->isProtected(),
                $parentClassConstant->isPrivate()
            );
            // If the constant isn't declared in the parent, it might be declared in the parent's parent
        }

        $parentClassConstantReflection = $this->parentConstantReflectionResolver->resolve($class, $constant);
        if ($parentClassConstantReflection === null) {
            return null;
        }

        return new ConstantVisibility(
            $parentClassConstantReflection->isPublic(),
            $parentClassConstantReflection->isProtected(),
            $parentClassConstantReflection->isPrivate()
        );
    }

    /**
     * @param string[] $directUseClasses
     * @param string[] $indirectUseClasses
     */
    private function changeConstantVisibility(
        ClassConst $classConst,
        array $directUseClasses,
        array $indirectUseClasses,
        ?ConstantVisibility $constantVisibility,
        string $class
    ): void {
        // 1. is actually never used
        if ($directUseClasses === []) {
            if ($indirectUseClasses !== [] && $constantVisibility !== null) {
                $this->makePrivateOrWeaker($classConst, $constantVisibility);
            }

            return;
        }

        // 2. is only local use? → private
        if ($directUseClasses === [$class]) {
            if ($indirectUseClasses === []) {
                $this->makePrivateOrWeaker($classConst, $constantVisibility);
            }

            return;
        }

        // 3. used by children → protected
        if ($this->isUsedByChildrenOnly($directUseClasses, $class)) {
            $this->makeProtected($classConst);
        } else {
            $this->makePublic($classConst);
        }
    }

    private function makePrivateOrWeaker(ClassConst $classConst, ?ConstantVisibility $parentConstantVisibility): void
    {
        if ($parentConstantVisibility !== null && $parentConstantVisibility->isProtected()) {
            $this->makeProtected($classConst);
        } elseif ($parentConstantVisibility !== null && $parentConstantVisibility->isPrivate() && ! $parentConstantVisibility->isProtected()) {
            $this->makePrivate($classConst);
        } elseif ($parentConstantVisibility === null) {
            $this->makePrivate($classConst);
        }
    }

    /**
     * @param string[] $useClasses
     */
    private function isUsedByChildrenOnly(array $useClasses, string $class): bool
    {
        $isChild = false;

        foreach ($useClasses as $useClass) {
            if (is_a($useClass, $class, true)) {
                $isChild = true;
            } else {
                // not a child, must be public
                return false;
            }
        }

        return $isChild;
    }
}
