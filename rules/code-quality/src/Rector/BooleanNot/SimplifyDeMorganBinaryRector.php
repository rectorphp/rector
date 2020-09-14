<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\BooleanNot;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BooleanNot;
use Rector\Core\PhpParser\Node\Manipulator\BinaryOpManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://robots.thoughtbot.com/clearer-conditionals-using-de-morgans-laws
 * @see https://stackoverflow.com/questions/20043664/de-morgans-law
 * @see \Rector\CodeQuality\Tests\Rector\BooleanNot\SimplifyDeMorganBinaryRector\SimplifyDeMorganBinaryRectorTest
 */
final class SimplifyDeMorganBinaryRector extends AbstractRector
{
    /**
     * @var BinaryOpManipulator
     */
    private $binaryOpManipulator;

    public function __construct(BinaryOpManipulator $binaryOpManipulator)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Simplify negated conditions with de Morgan theorem', [
            new CodeSample(
                <<<'CODE_SAMPLE'
<?php

$a = 5;
$b = 10;
$result = !($a > 20 || $b <= 50);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
<?php

$a = 5;
$b = 10;
$result = $a <= 20 && $b > 50;
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [BooleanNot::class];
    }

    /**
     * @param BooleanNot $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof BinaryOp) {
            return null;
        }

        // and is simpler to read → keep it
        if ($node->expr instanceof BooleanAnd) {
            return null;
        }

        return $this->binaryOpManipulator->inverseBinaryOp($node->expr);
    }
}
