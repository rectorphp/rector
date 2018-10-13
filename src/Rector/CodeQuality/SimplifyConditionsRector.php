<?php declare(strict_types=1);

namespace Rector\Rector\CodeQuality;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BooleanNot;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyConditionsRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $binaryOpClassMap = [
        Identical::class => NotIdentical::class,
        NotIdentical::class => Identical::class,
        Equal::class => NotEqual::class,
        NotEqual::class => Equal::class,
        Greater::class => SmallerOrEqual::class,
        Smaller::class => GreaterOrEqual::class,
        GreaterOrEqual::class => Smaller::class,
        SmallerOrEqual::class => Greater::class,
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Simplify conditions',
            [new CodeSample("if (! (\$foo !== 'bar')) {...", "if (\$foo === 'bar') {...")]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [BooleanNot::class];
    }

    /**
     * @param BooleanNot $notNode
     */
    public function refactor(Node $notNode): ?Node
    {
        if (! $notNode->expr instanceof BinaryOp) {
            return $notNode;
        }

        $binaryOpType = get_class($notNode->expr);

        if (! isset($this->binaryOpClassMap[$binaryOpType])) {
            return $notNode;
        }

        $newBinaryOp = $this->binaryOpClassMap[$binaryOpType];

        return new $newBinaryOp($notNode->expr->left, $notNode->expr->right);
    }
}
