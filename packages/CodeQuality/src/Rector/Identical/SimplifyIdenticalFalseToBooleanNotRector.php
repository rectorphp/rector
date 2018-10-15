<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use Rector\NodeAnalyzer\ConstFetchAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyIdenticalFalseToBooleanNotRector extends AbstractRector
{
    /**
     * @var ConstFetchAnalyzer
     */
    private $constFetchAnalyzer;

    public function __construct(ConstFetchAnalyzer $constFetchAnalyzer)
    {
        $this->constFetchAnalyzer = $constFetchAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes === false to negate !', [
            new CodeSample('if ($something === false) {}', 'if (! $something) {}'),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Identical::class];
    }

    /**
     * @param Identical $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->constFetchAnalyzer->isFalse($node->right)) {
            $comparedNode = $node->left;
            $shouldUnwrap = $node->left instanceof BooleanNot;
        } elseif ($this->constFetchAnalyzer->isFalse($node->left)) {
            $comparedNode = $node->right;
            $shouldUnwrap = $node->right instanceof BooleanNot;
        } else {
            return $node;
        }

        if ($shouldUnwrap) {
            /** @var BooleanNot $comparedNode */
            $comparedNode = $comparedNode->expr;
            if ($this->shouldSkip($comparedNode)) {
                return $node;
            }

            return $comparedNode;
        }

        if ($this->shouldSkip($comparedNode)) {
            return $node;
        }

        return new BooleanNot($comparedNode);
    }

    private function shouldSkip(Node $node): bool
    {
        if ($node instanceof BinaryOp) {
            return true;
        }

        return false;
    }
}
