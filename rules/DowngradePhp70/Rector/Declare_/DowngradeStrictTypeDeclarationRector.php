<?php

declare(strict_types=1);

namespace Rector\DowngradePhp70\Rector\Declare_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Declare_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp70\Rector\Declare_\DowngradeStrictTypeDeclarationRector\DowngradeStrictTypeDeclarationRectorTest
 */
final class DowngradeStrictTypeDeclarationRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Declare_::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove the declare(strict_types=1)',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
declare(strict_types=1);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        dump($node);
        if ($this->shouldSkip($node)) {
            return null;
        }

        return $node;
    }

    private function shouldSkip(FuncCall $funcCall): bool
    {
        if (! $this->isName($funcCall, 'declare')) {
            return true;
        }

        $args   = $funcCall->args;
        $assign = $args[0];

        dump($assign);
        return true;
    }
}
