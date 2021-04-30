<?php

declare(strict_types=1);

namespace Rector\DowngradePhp70\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Const_ as StmtConst_;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Tests\DowngradePhp70\Rector\Declare_\DowngradeDefineArrayConstantRector\DowngradeDefineArrayConstantRectorTest
 */
final class DowngradeDefineArrayConstantRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change array contant definition via define to const',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
define('ANIMALS', [
    'dog',
    'cat',
    'bird'
]);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
const ANIMALS = [
    'dog',
    'cat',
    'bird'
];
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        return new Const_(
            new Name(
                'const ' . $node->args[0]->value->value
            ),
            $node->args[1]->value
        );
    }

    private function shouldSkip(FuncCall $funcCall): bool
    {
        if (! $this->isName($funcCall, 'define')) {
            return true;
        }

        $args = $funcCall->args;
        return ! $args[1]->value instanceof Array_;
    }
}
