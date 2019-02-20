<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Equal;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class UseIdenticalOverEqualWithSameTypeRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use ===/!== over ==/!=, it values have the same type', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(int $firstValue, int $secondValue)
    {
         $isSame = $firstValue == $secondValue;
         $isDiffernt = $firstValue != $secondValue;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(int $firstValue, int $secondValue)
    {
         $isSame = $firstValue === $secondValue;
         $isDiffernt = $firstValue !== $secondValue;
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
        return [Equal::class, NotEqual::class];
    }

    /**
     * @param Equal|NotEqual $node
     */
    public function refactor(Node $node): ?Node
    {
        $leftStaticType = $this->getStaticType($node->left);
        $rightStaticType = $this->getStaticType($node->right);

        // objects can be different by content
        if ($leftStaticType instanceof ObjectType) {
            return null;
        }

        if ($leftStaticType === null || $rightStaticType === null) {
            return null;
        }

        // different types
        if (! $leftStaticType->equals($rightStaticType)) {
            return null;
        }

        if ($node instanceof Equal) {
            return new Identical($node->left, $node->right);
        }

        if ($node instanceof NotEqual) {
            return new NotIdentical($node->left, $node->right);
        }

        return $node;
    }
}
