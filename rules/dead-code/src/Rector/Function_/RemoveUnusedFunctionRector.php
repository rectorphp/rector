<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Function_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeCollector\NodeCollector\ParsedFunctionLikeNodeCollector;

/**
 * @see \Rector\DeadCode\Tests\Rector\Function_\RemoveUnusedFunctionRector\RemoveUnusedFunctionRectorTest
 */
final class RemoveUnusedFunctionRector extends AbstractRector implements ZeroCacheRectorInterface
{
    /**
     * @var ParsedFunctionLikeNodeCollector
     */
    private $parsedFunctionLikeNodeCollector;

    public function __construct(ParsedFunctionLikeNodeCollector $parsedFunctionLikeNodeCollector)
    {
        $this->parsedFunctionLikeNodeCollector = $parsedFunctionLikeNodeCollector;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove unused function', [
            new CodeSample(
                <<<'PHP'
function removeMe()
{
}

function useMe()
{
}

useMe();
PHP
,
                <<<'PHP'
function useMe()
{
}

useMe();
PHP

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

        if ($this->parsedFunctionLikeNodeCollector->isFunctionUsed($functionName)) {
            return null;
        }

        $this->removeNode($node);

        return $node;
    }
}
