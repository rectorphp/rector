<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\CodeQuality;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class UnnecessaryTernaryExpressionRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Remove unnecessary ternary expressions.',
            [new CodeSample('$foo === $bar ? true : false;', '$foo === $bar;')]
        );
    }

    public function isCandidate(Node $node): bool
    {
        return true;
    }

    public function refactor(Node $node): ?Node
    {
        return $node;
    }
}
