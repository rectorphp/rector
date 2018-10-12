<?php declare(strict_types=1);

namespace Rector\Rector\CodeQuality;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
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
        BinaryOp\Identical::class => BinaryOp\NotIdentical::class,
        BinaryOp\NotIdentical::class => BinaryOp\Identical::class,
        BinaryOp\Equal::class => BinaryOp\NotEqual::class,
        BinaryOp\NotEqual::class => BinaryOp\Equal::class,
        BinaryOp\Greater::class => BinaryOp\SmallerOrEqual::class,
        BinaryOp\Smaller::class => BinaryOp\GreaterOrEqual::class,
        BinaryOp\GreaterOrEqual::class => BinaryOp\Smaller::class,
        BinaryOp\SmallerOrEqual::class => BinaryOp\Greater::class,
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

        $newBinaryOp = $this->binaryOpClassMap[get_class($notNode->expr)];

        return new $newBinaryOp($notNode->expr->left, $notNode->expr->right);
    }
}
