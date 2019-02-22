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
    private function joinConcatIfStrings(Concat $concat): Node
    {
        if ($concat->left instanceof Concat) {
            $concat->left = $this->joinConcatIfStrings($concat->left);
        }

        if ($concat->right instanceof Concat) {
            $concat->right = $this->joinConcatIfStrings($concat->right);
        }

        if (! $concat->left instanceof String_) {
            return $concat;
        }

        if (! $concat->right instanceof String_) {
            return $concat;
        }

        return new String_($concat->left->value . $concat->right->value);
    }
}
