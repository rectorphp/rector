<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see http://php.net/manual/en/function.implode.php#refsect1-function.implode-description
 * @see https://3v4l.org/iYTgh
 * @see \Rector\CodingStyle\Tests\Rector\FuncCall\ConsistentImplodeRector\ConsistentImplodeRectorTest
 */
final class ConsistentImplodeRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes various implode forms to consistent one', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run(array $items)
    {
        $itemsAsStrings = implode($items);
        $itemsAsStrings = implode($items, '|');

        $itemsAsStrings = implode('|', $items);
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run(array $items)
    {
        $itemsAsStrings = implode('', $items);
        $itemsAsStrings = implode('|', $items);

        $itemsAsStrings = implode('|', $items);
    }
}
PHP
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
        if (! $this->isName($node, 'implode')) {
            return null;
        }

        if (count($node->args) === 1) {
            // complete default value ''
            $node->args[1] = $node->args[0];
            $node->args[0] = new Arg(new String_(''));

            return $node;
        }

        if (count($node->args) === 2) {
            if ($this->isStringOrUnionStringOnlyType($node->args[1]->value)) {
                $node->args = array_reverse($node->args);
            }
        }

        return $node;
    }
}
