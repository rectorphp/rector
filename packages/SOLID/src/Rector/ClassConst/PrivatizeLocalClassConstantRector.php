<?php declare(strict_types=1);

namespace Rector\SOLID\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\SOLID\Analyzer\ClassConstantFetchAnalyzer;

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

    /**
     * @var bool
     */
    private $keepDeclaredVisibility;

    public function __construct(
        ParsedNodesByType $parsedNodesByType,
        ClassConstantFetchAnalyzer $classConstantFetchAnalyzer,
        bool $keepDeclaredVisibility = false
    ) {
        $this->parsedNodesByType = $parsedNodesByType;
        $this->classConstantFetchAnalyzer = $classConstantFetchAnalyzer;
        $this->keepDeclaredVisibility = $keepDeclaredVisibility;
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
        $parentConstIsProtected = false;

        $parentClassConstant = $this->findParentClassConstant($class, $constant);
        if ($parentClassConstant !== null) {
            // The parent's constant is public, so this one must become public too
            if ($parentClassConstant->isPublic()) {
                $this->makePublic($node);
                return $node;
            }

            $parentConstIsProtected = $parentClassConstant->isProtected();
        }

        $useClasses = $this->findClassConstantFetches($class, $constant);

        return $this->changeConstantVisibility($node, $useClasses, $parentConstIsProtected, $class);
    }

    private function findParentClassConstant(string $class, string $constant): ?ClassConst
    {
        $classNode = $this->parsedNodesByType->findClass($class);
        if ($classNode !== null && $classNode->hasAttribute(AttributeKey::PARENT_CLASS_NAME)) {
            /** @var string $parentClassName */
            $parentClassName = $classNode->getAttribute(AttributeKey::PARENT_CLASS_NAME);
            if ($parentClassName) {
                $parentClassConstant = $this->parsedNodesByType->findClassConstant($parentClassName, $constant);
                if ($parentClassConstant) {
                    // Make sure the parent's constant has been refactored
                    $this->refactor($parentClassConstant);

                    return $parentClassConstant;
                }
                // If the constant isn't declared in the parent, it might be declared in the parent's parent
                return $this->findParentClassConstant($parentClassName, $constant);
            }
        }

        return null;
    }

    private function makePrivateOrWeaker(ClassConst $classConst, bool $protectedRequired): void
    {
        if ($protectedRequired) {
            $this->makeProtected($classConst);
        } else {
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
        bool $parentConstIsProtected,
        string $class
    ): Node {
        // 1. is actually never used (@todo use in "dead-code" set)
        if ($useClasses === null) {
            $this->makePrivateOrWeaker($classConst, $parentConstIsProtected);
            return $classConst;
        }

        // 2. is only local use? → private
        if ($useClasses === [$class]) {
            $this->makePrivateOrWeaker($classConst, $parentConstIsProtected);
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

    private function shouldSkip(ClassConst $classConst): bool
    {
        if ($classConst->getAttribute(self::HAS_NEW_ACCESS_LEVEL)) {
            return true;
        }

        if (! $this->isAtLeastPhpVersion('7.1')) {
            return true;
        }

        if ($this->keepDeclaredVisibility && ($classConst->flags & Class_::VISIBILITY_MODIFIER_MASK) !== 0) {
            return true;
        }

        return count($classConst->consts) !== 1;
    }
}
