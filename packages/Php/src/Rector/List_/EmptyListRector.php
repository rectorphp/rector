<?php declare(strict_types=1);

namespace Rector\Php\Rector\List_;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @source http://php.net/manual/en/migration70.incompatible.php#migration70.incompatible.variable-handling.list
 */
final class EmptyListRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'list() cannot be empty',
            [new CodeSample('list() = $values;', 'list($generated) = $values;')]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [List_::class];
    }

    /**
     * @param List_ $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($node->items as $item) {
            if ($item !== null) {
                return null;
            }
        }

        $node->items[0] = new ArrayItem(new Variable('unusedGenerated'));

        return $node;
    }
}
