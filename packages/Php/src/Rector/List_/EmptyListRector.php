<?php declare(strict_types=1);

namespace Rector\Php\Rector\List_;

use PhpParser\Node;
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
     * @param List_ $listNode
     */
    public function refactor(Node $listNode): ?Node
    {
        foreach ($listNode->items as $item) {
            if ($item !== null) {
                return $listNode;
            }
        }

        $listNode->items[0] = new Variable('unusedGenerated');

        return $listNode;
    }
}
