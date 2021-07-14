<?php

declare(strict_types=1);

namespace Rector\DowngradePhp70\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeNestingScope\ParentFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp70\Rector\Expression\DowngradeDefineArrayConstantRector\DowngradeDefineArrayConstantRectorTest
 */
final class DowngradeDefineArrayConstantRector extends AbstractRector
{
    public function __construct(
        private ParentFinder $parentFinder
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Expression::class];
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
     * @param Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof FuncCall) {
            return null;
        }

        $funcCall = $node->expr;

        if ($this->shouldSkip($funcCall)) {
            return null;
        }

        /** @var String_ $arg0 */
        $arg0 = $funcCall->args[0]->value;
        $arg0Value = $arg0->value;

        /** @var Array_ $arg1Value */
        $arg1Value = $funcCall->args[1]->value;

        return new Node\Stmt\Const_([new Const_($arg0Value, $arg1Value)]);
    }

    private function shouldSkip(FuncCall $funcCall): bool
    {
        if (! $this->isName($funcCall, 'define')) {
            return true;
        }

        $args = $funcCall->args;
        if (! $args[0]->value instanceof String_) {
            return true;
        }

        if (! $args[1]->value instanceof Array_) {
            return true;
        }

        return (bool) $this->parentFinder->findByTypes($funcCall, [ClassMethod::class, Function_::class]);
    }
}
