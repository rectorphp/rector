<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\NodeContainer\ParsedNodesByType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\SOLID\Analyzer\ClassConstantFetchAnalyzer;
use Rector\SOLID\ValueObject\ConstantVisibility;
use ReflectionClass;
use ReflectionClassConstant;

/**
 * @see \Rector\SOLID\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\PrivatizeLocalClassConstantRectorTest
 */
final class PrivatizeLocalClassConstantRector extends AbstractRector
{
    /**
     * @var string
     */
    public const HAS_NEW_ACCESS_LEVEL = 'has_new_access_level';

    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    /**
     * @var ClassConstantFetchAnalyzer
     */
    private $classConstantFetchAnalyzer;

    public function __construct(
        ParsedNodesByType $parsedNodesByType,
        ClassConstantFetchAnalyzer $classConstantFetchAnalyzer
    ) {
        $this->parsedNodesByType = $parsedNodesByType;
        $this->classConstantFetchAnalyzer = $classConstantFetchAnalyzer;
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
        if ($this->parsedNodesByType->findInterface($class) !== null) {
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

        $useClasses = $this->findClassConstantFetches($class, $constant);

        return $this->changeConstantVisibility($node, $useClasses, $parentClassConstantVisibility, $class);
    }

    private function shouldSkip(ClassConst $classConst): bool
    {
        if ($classConst->getAttribute(self::HAS_NEW_ACCESS_LEVEL)) {
            return true;
        }

        if (! $this->isAtLeastPhpVersion('7.1')) {
            return true;
        }

        return count($classConst->consts) !== 1;
    }

    private function findParentClassConstantAndRefactorIfPossible(string $class, string $constant): ?ConstantVisibility
    {
        $classNode = $this->parsedNodesByType->findClass($class);
        if ($classNode !== null && $classNode->hasAttribute(AttributeKey::PARENT_CLASS_NAME)) {
            /** @var string $parentClass */
            $parentClass = $classNode->getAttribute(AttributeKey::PARENT_CLASS_NAME);

            if ($parentClass !== '') {
                $parentClassConstant = $this->parsedNodesByType->findClassConstant($parentClass, $constant);
                if ($parentClassConstant !== null) {
                    // Make sure the parent's constant has been refactored
                    $this->refactor($parentClassConstant);

                    return new ConstantVisibility(
                        $parentClassConstant->isPublic(),
                        $parentClassConstant->isProtected(),
                        $parentClassConstant->isPrivate()
                    );
                }

                // If the constant isn't declared in the parent, it might be declared in the parent's parent
            }
        }

        $reflectionClass = new ReflectionClass($class);
        if (! $reflectionClass->getParentClass()) {
            return null;
        }

        $parentClass = $reflectionClass->getParentClass();
        if (! $parentClass) {
            return null;
        }

        /** @var ReflectionClass $parentClass */
        $parentClassConstantReflection = $this->resolveParentClassConstantReflection(
            $parentClass->getName(),
            $constant
        );
        if ($parentClassConstantReflection === null) {
            return null;
        }

        return new ConstantVisibility(
            $parentClassConstantReflection->isPublic(),
            $parentClassConstantReflection->isProtected(),
            $parentClassConstantReflection->isPrivate()
        );
    }

    private function findClassConstantFetches(string $className, string $constantName): ?array
    {
        $classConstantFetchByClassAndName = $this->classConstantFetchAnalyzer->provideClassConstantFetchByClassAndName();

        return $classConstantFetchByClassAndName[$className][$constantName] ?? null;
    }

    /**
     * @param string[]|null $useClasses
     */
    private function changeConstantVisibility(
        ClassConst $classConst,
        ?array $useClasses,
        ?ConstantVisibility $constantVisibility,
        string $class
    ): Node {
        // 1. is actually never used (@todo use in "dead-code" set)
        if ($useClasses === null) {
            $this->makePrivateOrWeaker($classConst, $constantVisibility);
            return $classConst;
        }

        // 2. is only local use? → private
        if ($useClasses === [$class]) {
            $this->makePrivateOrWeaker($classConst, $constantVisibility);
            return $classConst;
        }

        // 3. used by children → protected
        if ($this->isUsedByChildrenOnly($useClasses, $class)) {
            $this->makeProtected($classConst);
        } else {
            $this->makePublic($classConst);
        }

        return $classConst;
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

    private function resolveParentClassConstantReflection(string $class, string $constant): ?ReflectionClassConstant
    {
        $parentReflectionClass = new ReflectionClass($class);

        while ($parentReflectionClass !== false) {
            if ($parentReflectionClass->hasConstant($constant)) {
                return $parentReflectionClass->getReflectionConstant($constant);
            }

            $parentReflectionClass = $parentReflectionClass->getParentClass();
        }

        return null;
    }
}
