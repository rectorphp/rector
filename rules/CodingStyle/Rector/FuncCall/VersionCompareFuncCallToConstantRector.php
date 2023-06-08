<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

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
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\PhpVersionFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector\VersionCompareFuncCallToConstantRectorTest
 */
final class VersionCompareFuncCallToConstantRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\Util\PhpVersionFactory
     */
    private $phpVersionFactory;
    /**
     * @var array<string, class-string<BinaryOp>>
     */
    private const OPERATOR_TO_COMPARISON = ['=' => Identical::class, '==' => Identical::class, 'eq' => Identical::class, '!=' => NotIdentical::class, '<>' => NotIdentical::class, 'ne' => NotIdentical::class, '>' => Greater::class, 'gt' => Greater::class, '<' => Smaller::class, 'lt' => Smaller::class, '>=' => GreaterOrEqual::class, 'ge' => GreaterOrEqual::class, '<=' => SmallerOrEqual::class, 'le' => SmallerOrEqual::class];
    public function __construct(PhpVersionFactory $phpVersionFactory)
    {
        $this->phpVersionFactory = $phpVersionFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes use of call to version compare function to use of PHP version constant', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'version_compare')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (\count($node->getArgs()) !== 3) {
            return null;
        }
        $args = $node->getArgs();
        if (!$this->isPhpVersionConstant($args[0]->value) && !$this->isPhpVersionConstant($args[1]->value)) {
            return null;
        }
        $left = $this->getNewNodeForArg($args[0]->value);
        $right = $this->getNewNodeForArg($args[1]->value);
        if (!$left instanceof Expr) {
            return null;
        }
        if (!$right instanceof Expr) {
            return null;
        }
        /** @var String_ $operator */
        $operator = $args[2]->value;
        $comparisonClass = self::OPERATOR_TO_COMPARISON[$operator->value];
        return new $comparisonClass($left, $right);
    }
    private function isPhpVersionConstant(Expr $expr) : bool
    {
        if (!$expr instanceof ConstFetch) {
            return \false;
        }
        return $expr->name->toString() === 'PHP_VERSION';
    }
    /**
     * @return \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Scalar\LNumber|null
     */
    private function getNewNodeForArg(Expr $expr)
    {
        if ($this->isPhpVersionConstant($expr)) {
            return new ConstFetch(new Name('PHP_VERSION_ID'));
        }
        return $this->getVersionNumberFormVersionString($expr);
    }
    private function getVersionNumberFormVersionString(Expr $expr) : ?LNumber
    {
        if (!$expr instanceof String_) {
            return null;
        }
        $value = $this->phpVersionFactory->createIntVersion($expr->value);
        return new LNumber($value);
    }
}
