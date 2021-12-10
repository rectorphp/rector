<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\Ternary;
use PHPStan\Type\BooleanType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Ternary\SimplifyDuplicatedTernaryRector\SimplifyDuplicatedTernaryRectorTest
 */
final class SimplifyDuplicatedTernaryRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove ternary that duplicated return value of true : false', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(bool $value, string $name)
    {
         $isTrue = $value ? true : false;
         $isName = $name ? true : false;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(bool $value, string $name)
    {
         $isTrue = $value;
         $isName = $name ? true : false;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $condType = $this->getType($node->cond);
        if (!$condType instanceof \PHPStan\Type\BooleanType) {
            return null;
        }
        if ($node->if === null) {
            return null;
        }
        if (!$this->valueResolver->isTrue($node->if)) {
            return null;
        }
        if (!$this->valueResolver->isFalse($node->else)) {
            return null;
        }
        return $node->cond;
    }
}
