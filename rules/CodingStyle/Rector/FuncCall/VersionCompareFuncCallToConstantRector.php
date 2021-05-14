<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

use RectorPrefix20210514\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector\VersionCompareFuncCallToConstantRectorTest
 */
final class VersionCompareFuncCallToConstantRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, class-string<BinaryOp>>
     */
    private const OPERATOR_TO_COMPARISON = ['=' => \PhpParser\Node\Expr\BinaryOp\Identical::class, '==' => \PhpParser\Node\Expr\BinaryOp\Identical::class, 'eq' => \PhpParser\Node\Expr\BinaryOp\Identical::class, '!=' => \PhpParser\Node\Expr\BinaryOp\NotIdentical::class, '<>' => \PhpParser\Node\Expr\BinaryOp\NotIdentical::class, 'ne' => \PhpParser\Node\Expr\BinaryOp\NotIdentical::class, '>' => \PhpParser\Node\Expr\BinaryOp\Greater::class, 'gt' => \PhpParser\Node\Expr\BinaryOp\Greater::class, '<' => \PhpParser\Node\Expr\BinaryOp\Smaller::class, 'lt' => \PhpParser\Node\Expr\BinaryOp\Smaller::class, '>=' => \PhpParser\Node\Expr\BinaryOp\GreaterOrEqual::class, 'ge' => \PhpParser\Node\Expr\BinaryOp\GreaterOrEqual::class, '<=' => \PhpParser\Node\Expr\BinaryOp\SmallerOrEqual::class, 'le' => \PhpParser\Node\Expr\BinaryOp\SmallerOrEqual::class];
    /**
     * @var string
     * @see https://regex101.com/r/yl9g25/1
     */
    private const SEMANTIC_VERSION_REGEX = '#^\\d+\\.\\d+\\.\\d+$#';
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes use of call to version compare function to use of PHP version constant', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        version_compare(PHP_VERSION, '5.3.0', '<');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        PHP_VERSION_ID < 50300;
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
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node, 'version_compare')) {
            return null;
        }
        if (\count($node->args) !== 3) {
            return null;
        }
        if (!$this->isPhpVersionConstant($node->args[0]->value) && !$this->isPhpVersionConstant($node->args[1]->value)) {
            return null;
        }
        $left = $this->getNewNodeForArg($node->args[0]->value);
        $right = $this->getNewNodeForArg($node->args[1]->value);
        /** @var String_ $operator */
        $operator = $node->args[2]->value;
        $comparisonClass = self::OPERATOR_TO_COMPARISON[$operator->value];
        return new $comparisonClass($left, $right);
    }
    private function isPhpVersionConstant(\PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\ConstFetch) {
            return \false;
        }
        return $expr->name->toString() === 'PHP_VERSION';
    }
    private function getNewNodeForArg(\PhpParser\Node\Expr $expr) : \PhpParser\Node\Expr
    {
        if ($this->isPhpVersionConstant($expr)) {
            return new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('PHP_VERSION_ID'));
        }
        return $this->getVersionNumberFormVersionString($expr);
    }
    private function getVersionNumberFormVersionString(\PhpParser\Node\Expr $expr) : \PhpParser\Node\Scalar\LNumber
    {
        if (!$expr instanceof \PhpParser\Node\Scalar\String_) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        if (!\RectorPrefix20210514\Nette\Utils\Strings::match($expr->value, self::SEMANTIC_VERSION_REGEX)) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $versionParts = \explode('.', $expr->value);
        return new \PhpParser\Node\Scalar\LNumber((int) $versionParts[0] * 10000 + (int) $versionParts[1] * 100 + (int) $versionParts[2]);
    }
}
