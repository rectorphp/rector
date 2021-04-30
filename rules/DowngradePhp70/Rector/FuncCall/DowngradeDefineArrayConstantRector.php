<?php

declare(strict_types=1);

namespace Rector\DowngradePhp70\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeDefineArrayConstantRector\DowngradeDefineArrayConstantRectorTest
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

        $arg0 = $node->args[0]->value;
        if (! $arg0 instanceof String_) {
            return null;
        }

        $arg0Value = $arg0->value;
        /** @var Array_ $arg1Value */
        $arg1Value = $node->args[1]->value;

        return new Const_('const ' . $arg0Value, $arg1Value);
    }

    private function shouldSkip(FuncCall $funcCall): bool
    {
        if (! $this->isName($funcCall, 'define')) {
            return true;
        }

        $args = $funcCall->args;
        if (! $args[1]->value instanceof Array_) {
            return true;
        }

        $methodNode = $funcCall->getAttribute(AttributeKey::METHOD_NODE);
        if ($methodNode instanceof ClassMethod) {
            return true;
        }

        $parent = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Expression || $parent->expr !== $funcCall) {
            return true;
        }

        return (bool) $this->betterNodeFinder->findParentType($funcCall, Function_::class);
    }
}
