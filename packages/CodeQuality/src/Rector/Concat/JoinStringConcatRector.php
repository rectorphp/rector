<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Concat;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class JoinStringConcatRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Joins concat of 2 strings', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $name = 'Hi' . ' Tom';
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $name = 'Hi Tom';
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
        return [Concat::class];
    }

    /**
     * @param Concat $node
     */
    public function refactor(Node $node): ?Node
    {
        return $this->joinConcatIfStrings($node);
    }

    /**
     * @return Concat|String_
     */
    private function joinConcatIfStrings(Concat $node): Node
    {
        if ($node->left instanceof Concat) {
            $node->left = $this->joinConcatIfStrings($node->left);
        }

        if ($node->right instanceof Concat) {
            $node->right = $this->joinConcatIfStrings($node->right);
        }

        if (! $node->left instanceof String_) {
            return $node;
        }

        if (! $node->right instanceof String_) {
            return $node;
        }

        return new String_($node->left->value . $node->right->value);
    }
}
