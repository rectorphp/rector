<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Coalesce;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\Ternary;
use PHPStan\Type\ErrorType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\UnionType;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Coalesce\CoalesceToTernaryRector\CoalesceToTernaryRectorTest
 */
final class CoalesceToTernaryRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace coalesce to ternary when left side is non nullable', [new CodeSample(<<<'CODE_SAMPLE'
function run(string $a)
{
	return $a ?? 'foo';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function run(string $a)
{
	return $a ?: 'foo';
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Coalesce::class];
    }
    /**
     * @param Coalesce $node
     */
    public function refactor(Node $node): ?Node
    {
        /**
         * indexed data maybe false positive
         */
        if ($node->left instanceof ArrayDimFetch) {
            return null;
        }
        /**
         * Scope needs to use parent Coalesce to properly get type from left side of coalesce
         */
        $scope = ScopeFetcher::fetch($node);
        $nativeType = $scope->getNativeType($node->left);
        if ($nativeType instanceof ErrorType) {
            return null;
        }
        if ($nativeType instanceof MixedType) {
            return null;
        }
        if ($nativeType instanceof NullType) {
            return null;
        }
        if ($nativeType instanceof UnionType) {
            foreach ($nativeType->getTypes() as $unionedType) {
                if ($unionedType instanceof NullType) {
                    return null;
                }
            }
        }
        return new Ternary($node->left, null, $node->right);
    }
}
