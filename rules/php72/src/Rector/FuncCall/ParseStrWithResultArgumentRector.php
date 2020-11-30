<?php

declare(strict_types=1);

namespace Rector\Php72\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://3v4l.org/u5pes
 * @see https://github.com/gueff/blogimus/commit/04086a10320595470efe446c7ddd90e602aa7228
 * @see https://github.com/pxgamer/youtube-dl-php/commit/83cb32b8b36844f2e39f82a862a5ab73da77b608
 * @see \Rector\Php72\Tests\Rector\FuncCall\ParseStrWithResultArgumentRector\ParseStrWithResultArgumentRectorTest
 */
final class ParseStrWithResultArgumentRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Use $result argument in parse_str() function',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
parse_str($this->query);
$data = get_defined_vars();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
parse_str($this->query, $result);
$data = $result;
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'parse_str')) {
            return null;
        }

        if (isset($node->args[1])) {
            return null;
        }

        $resultVariable = new Variable('result');
        $node->args[1] = new Arg($resultVariable);

        $expression = $node->getAttribute(AttributeKey::CURRENT_STATEMENT);
        if ($expression === null) {
            return null;
        }

        $nextExpression = $expression->getAttribute(AttributeKey::NEXT_NODE);
        if ($nextExpression === null) {
            return null;
        }

        $this->traverseNodesWithCallable($nextExpression, function (Node $node) use ($resultVariable): ?Variable {
            if ($this->isFuncCallName($node, 'get_defined_vars')) {
                return $resultVariable;
            }

            return null;
        });

        return $node;
    }
}
