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
        if (! $this->isAtLeastPhpVersion('7.1')) {
            return null;
        }

        if (count($node->consts) > 1) {
            return null;
        }

        /** @var string $class */
        $class = $node->getAttribute(AttributeKey::CLASS_NAME);

        // 0. constants declared in interfaces have to be public
        if ($this->parsedNodesByType->findInterface($class) !== null) {
            $this->makePublic($node);
            return $node;
        }

        /** @var string $constant */
        $constant = $this->getName($node);
        $useClasses = $this->findClassConstantFetches($class, $constant);

        // 1. is actually never used (@todo use in "dead-code" set)
        if ($useClasses === null) {
            $this->makePrivate($node);
            return $node;
        }

        // 2. is only local use? → private
        if ($useClasses === [$class]) {
            $this->makePrivate($node);
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
