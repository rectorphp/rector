<?php declare(strict_types=1);

namespace Rector\SOLID\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\SOLID\Analyzer\ClassConstantFetchAnalyzer;

final class PrivatizeLocalClassConstantRector extends AbstractRector
{
    const HAS_NEW_ACCESS_LEVEL = 'has_new_access_level';

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
        if ($node->getAttribute(self::HAS_NEW_ACCESS_LEVEL)) {
            return null;
        }

        if (! $this->isAtLeastPhpVersion('7.1')) {
            return null;
        }

        if (count($node->consts) > 1) {
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
        $classNode = $this->parsedNodesByType->findClass($class);
        $mustBeAtLeastProtected = false;

        if ($classNode !== null && $classNode->hasAttribute(AttributeKey::PARENT_CLASS_NAME)) {
            /** @var string $parentClassName */
            $parentClassName = $classNode->getAttribute(AttributeKey::PARENT_CLASS_NAME);
            if ($parentClassName) {
                $parentClassConstant = $this->parsedNodesByType->findClassConstant($parentClassName, $constant);
                if ($parentClassConstant) {
                    $this->refactor($parentClassConstant);

                    // The parent's constant is public, so this one must become public too
                    if ($parentClassConstant->isPublic()) {
                        $this->makePublic($node);
                        return $node;
                    }

                    // The parent's constant is protected, so this one must become protected or weaker
                    if ($parentClassConstant->isProtected()) {
                        $mustBeAtLeastProtected = true;
                    }
                }
            }
        }

        $useClasses = $this->findClassConstantFetches($class, $constant);

        // 1. is actually never used (@todo use in "dead-code" set)
        if ($useClasses === null) {
            if ($mustBeAtLeastProtected) {
                $this->makeProtected($node);
            } else {
                $this->makePrivate($node);
            }
            return $node;
        }

        // 2. is only local use? → private
        if ($useClasses === [$class]) {
            if ($mustBeAtLeastProtected) {
                $this->makeProtected($node);
            } else {
                $this->makePrivate($node);
            }
            return $node;
        }

        // 3. used by children → protected
        if ($this->isUsedByChildrenOnly($useClasses, $class)) {
            $this->makeProtected($node);
        } else {
            $this->makePublic($node);
        }

        return $node;
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
}
