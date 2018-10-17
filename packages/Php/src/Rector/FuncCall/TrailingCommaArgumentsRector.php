<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/trailing-comma-function-calls
 */
final class TrailingCommaArgumentsRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Adds trailing commas to function and methods calls ', [
            new CodeSample(
                <<<'CODE_SAMPLE'
calling(
    $one,
    $two
);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
calling(
    $one,
    $two,
);
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, MethodCall::class, StaticCall::class];
    }

    /**
     * @param FuncCall|MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! count($node->args)) {
            return null;
        }

        if ($node->getAttribute('trailingComma')) {
            return null;
        }

        $node->setAttribute('trailingComma', true);

        // invoke redraw
        $node->setAttribute(Attribute::ORIGINAL_NODE, null);

        return $node;
    }
}
