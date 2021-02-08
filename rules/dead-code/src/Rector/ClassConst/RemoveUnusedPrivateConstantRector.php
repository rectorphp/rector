<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\NodeManipulator\ClassConstManipulator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedPrivateConstantRector\RemoveUnusedPrivateConstantRectorTest
 */
final class RemoveUnusedPrivateConstantRector extends AbstractRector
{
    /**
     * @var ClassConstManipulator
     */
    private $classConstManipulator;

    public function __construct(ClassConstManipulator $classConstManipulator)
    {
        $this->classConstManipulator = $classConstManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused private constant', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeController
{
    private const SOME_CONSTANT = 5;
    public function run()
    {
        return 5;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeController
{
    public function run()
    {
        return 5;
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
        if (! $node->isPrivate()) {
            return null;
        }

        if (count($node->consts) !== 1) {
            return null;
        }

        // never used
        $classConstFetches = $this->classConstManipulator->getAllClassConstFetch($node);
        if ($classConstFetches !== []) {
            return null;
        }

        // skip enum
        if ($this->classConstManipulator->isEnum($node)) {
            return null;
        }

        $this->removeNode($node);

        return $node;
    }
}
