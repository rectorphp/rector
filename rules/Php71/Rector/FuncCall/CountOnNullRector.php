<?php

declare (strict_types=1);
namespace Rector\Php71\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\Cast\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\NodeAnalyzer\VariableAnalyzer;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\TypeAnalyzer\CountableTypeAnalyzer;
use Rector\Php71\NodeAnalyzer\CountableAnalyzer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://3v4l.org/Bndc9
 *
 * @see \Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\CountOnNullRectorTest
 */
final class CountOnNullRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeAnalyzer\CountableTypeAnalyzer
     */
    private $countableTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Php71\NodeAnalyzer\CountableAnalyzer
     */
    private $countableAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\VariableAnalyzer
     */
    private $variableAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\NodeTypeResolver\TypeAnalyzer\CountableTypeAnalyzer $countableTypeAnalyzer, \Rector\Php71\NodeAnalyzer\CountableAnalyzer $countableAnalyzer, \Rector\Core\NodeAnalyzer\VariableAnalyzer $variableAnalyzer, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->countableTypeAnalyzer = $countableTypeAnalyzer;
        $this->countableAnalyzer = $countableAnalyzer;
        $this->variableAnalyzer = $variableAnalyzer;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::COUNT_ON_NULL;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes count() on null to safe ternary check', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$values = null;
$count = count($values);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$values = null;
$count = count((array) $values);
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
        if ($this->shouldSkip($node)) {
            return null;
        }
        /** @var Arg $arg0 */
        $arg0 = $node->args[0];
        $countedNode = $arg0->value;
        if ($this->countableTypeAnalyzer->isCountableType($countedNode)) {
            return null;
        }
        // this can lead to false positive by phpstan, but that's best we can do
        $onlyValueType = $this->getType($countedNode);
        if ($onlyValueType instanceof \PHPStan\Type\ArrayType) {
            if (!$this->countableAnalyzer->isCastableArrayType($countedNode, $onlyValueType)) {
                return null;
            }
            return $this->castToArray($countedNode, $node);
        }
        if ($this->nodeTypeResolver->isNullableTypeOfSpecificType($countedNode, \PHPStan\Type\ArrayType::class)) {
            return $this->castToArray($countedNode, $node);
        }
        $countedType = $this->getType($countedNode);
        if ($this->isAlwaysIterableType($countedType)) {
            return null;
        }
        if ($this->nodeTypeResolver->isNullableType($countedNode) || $countedType instanceof \PHPStan\Type\NullType) {
            $identical = new \PhpParser\Node\Expr\BinaryOp\Identical($countedNode, $this->nodeFactory->createNull());
            return new \PhpParser\Node\Expr\Ternary($identical, new \PhpParser\Node\Scalar\LNumber(0), $node);
        }
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::IS_COUNTABLE)) {
            $conditionNode = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('is_countable'), [new \PhpParser\Node\Arg($countedNode)]);
        } else {
            $instanceof = new \PhpParser\Node\Expr\Instanceof_($countedNode, new \PhpParser\Node\Name\FullyQualified('Countable'));
            $conditionNode = new \PhpParser\Node\Expr\BinaryOp\BooleanOr($this->nodeFactory->createFuncCall('is_array', [new \PhpParser\Node\Arg($countedNode)]), $instanceof);
        }
        return new \PhpParser\Node\Expr\Ternary($conditionNode, $node, new \PhpParser\Node\Scalar\LNumber(0));
    }
    private function isAlwaysIterableType(\PHPStan\Type\Type $possibleUnionType) : bool
    {
        if ($possibleUnionType->isIterable()->yes()) {
            return \true;
        }
        if (!$possibleUnionType instanceof \PHPStan\Type\UnionType) {
            return \false;
        }
        $types = $possibleUnionType->getTypes();
        foreach ($types as $type) {
            if ($type->isIterable()->no()) {
                return \false;
            }
        }
        return \true;
    }
    private function shouldSkip(\PhpParser\Node\Expr\FuncCall $funcCall) : bool
    {
        if (!$this->isName($funcCall, 'count')) {
            return \true;
        }
        if (!isset($funcCall->args[0])) {
            return \true;
        }
        if (!$funcCall->args[0] instanceof \PhpParser\Node\Arg) {
            return \true;
        }
        if ($funcCall->args[0]->value instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return \true;
        }
        $parentNode = $funcCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentNode instanceof \PhpParser\Node\Expr\Ternary) {
            return \true;
        }
        // skip node in trait, as impossible to analyse
        $trait = $this->betterNodeFinder->findParentType($funcCall, \PhpParser\Node\Stmt\Trait_::class);
        if ($trait instanceof \PhpParser\Node\Stmt\Trait_) {
            return \true;
        }
        if (!$funcCall->args[0]->value instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        return $this->variableAnalyzer->isStaticOrGlobal($funcCall->args[0]->value);
    }
    private function castToArray(\PhpParser\Node\Expr $countedExpr, \PhpParser\Node\Expr\FuncCall $funcCall) : \PhpParser\Node\Expr\FuncCall
    {
        $castArray = new \PhpParser\Node\Expr\Cast\Array_($countedExpr);
        $funcCall->args = [new \PhpParser\Node\Arg($castArray)];
        return $funcCall;
    }
}
