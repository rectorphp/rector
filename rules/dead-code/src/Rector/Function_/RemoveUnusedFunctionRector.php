<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Function_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\Function_\RemoveUnusedFunctionRector\RemoveUnusedFunctionRectorTest
 */
final class RemoveUnusedFunctionRector extends AbstractRector implements ZeroCacheRectorInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused function', [
            new CodeSample(
                <<<'CODE_SAMPLE'
function removeMe()
{
}

function useMe()
{
}

useMe();
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
function useMe()
{
}

useMe();
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Function_::class];
    }

    /**
     * @param Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var string $functionName */
        $functionName = $this->getName($node);

        if ($this->nodeRepository->isFunctionUsed($functionName)) {
            return null;
        }

        $this->removeNode($node);

        return $node;
    }
}
