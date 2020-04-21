<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeCollector\NodeFinder\ClassConstParsedNodesFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedClassConstantRector\RemoveUnusedClassConstantRectorTest
 */
final class RemoveUnusedClassConstantRector extends AbstractRector implements ZeroCacheRectorInterface
{
    /**
     * @var ClassConstParsedNodesFinder
     */
    private $classConstParsedNodesFinder;

    public function __construct(ClassConstParsedNodesFinder $classConstParsedNodesFinder)
    {
        $this->classConstParsedNodesFinder = $classConstParsedNodesFinder;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove unused class constants', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    private const SOME_CONST = 'dead';

    public function run()
    {
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
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

        /** @var string $class */
        $class = $node->getAttribute(AttributeKey::CLASS_NAME);

        // 0. constants declared in interfaces have to be public
        if ($this->classLikeParsedNodesFinder->findInterface($class) !== null) {
            $this->makePublic($node);
            return $node;
        }

        /** @var string $constant */
        $constant = $this->getName($node);

        $directUseClasses = $this->classConstParsedNodesFinder->findDirectClassConstantFetches($class, $constant);
        if ($directUseClasses !== []) {
            return null;
        }

        $indirectUseClasses = $this->classConstParsedNodesFinder->findIndirectClassConstantFetches($class, $constant);
        if ($indirectUseClasses !== []) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }

    private function shouldSkip(Node $node): bool
    {
        if ($this->isOpenSourceProjectType()) {
            return true;
        }
        return count($node->consts) !== 1;
    }
}
