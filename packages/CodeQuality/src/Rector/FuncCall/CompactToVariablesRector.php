<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://stackoverflow.com/a/16319909/1348344
 * @see https://3v4l.org/8GJEs
 */
final class CompactToVariablesRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change compact() call to own array', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $checkout = 'one';
        $form = 'two';

        return compact('checkout', 'form');
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $checkout = 'one';
        $form = 'two';

        return ['checkout' => $checkout, 'form' => $form];
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
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'compact')) {
            return null;
        }

        $array = new Array_();

        foreach ($node->args as $arg) {
            $variableName = $this->getValue($arg->value);
            $array->items[] = new ArrayItem(new Variable($variableName), new String_($variableName));
        }

        return $array;
    }
}
