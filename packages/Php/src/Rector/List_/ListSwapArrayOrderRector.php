<?php declare(strict_types=1);

namespace Rector\Php\Rector\List_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Name;
use Rector\Printer\BetterStandardPrinter;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @source http://php.net/manual/en/migration70.incompatible.php#migration70.incompatible.variable-handling.list
 */
final class ListSwapArrayOrderRector extends AbstractRector
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(BetterStandardPrinter $betterStandardPrinter)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'list() assigns variables in reverse order - relevant in array assign',
            [new CodeSample('list($a[], $a[]) = [1, 2];', 'list($a[], $a[]) = array_reverse([1, 2])];')]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $assignNode
     */
    public function refactor(Node $assignNode): ?Node
    {
        if (! $assignNode->var instanceof List_) {
            return $assignNode;
        }

        $printerVars = [];

        /** @var ArrayItem $item */
        foreach ($assignNode->var->items as $item) {
            if ($item->value instanceof ArrayDimFetch) {
                $printerVars[] = $this->betterStandardPrinter->prettyPrint([$item->value->var]);
            } else {
                return $assignNode;
            }
        }

        // relevant only in 1 variable type
        if (count(array_unique($printerVars)) !== 1) {
            return $assignNode;
        }

        // wrap with array_reverse, to reflect reverse assign order in left
        $assignNode->expr = new FuncCall(new Name('array_reverse'), [new Arg($assignNode->expr)]);

        return $assignNode;
    }
}
