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
 * @changelog https://3v4l.org/Bndc9
 *
 * @see \Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\CountOnNullRectorTest
 */
final class CountOnNullRector extends AbstractRector implements MinPhpVersionInterface
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
    public function __construct(CountableTypeAnalyzer $countableTypeAnalyzer, CountableAnalyzer $countableAnalyzer, VariableAnalyzer $variableAnalyzer, PhpVersionProvider $phpVersionProvider)
    {
        $this->countableTypeAnalyzer = $countableTypeAnalyzer;
        $this->countableAnalyzer = $countableAnalyzer;
        $this->variableAnalyzer = $variableAnalyzer;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::COUNT_ON_NULL;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes count() on null to safe ternary check', [new CodeSample(<<<'CODE_SAMPLE'
$values = null;
$count = count($values);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$values = null;
$count = $values === null ? 0 : count($values);
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
        if ($onlyValueType instanceof ArrayType) {
            if (!$this->countableAnalyzer->isCastableArrayType($countedNode, $onlyValueType)) {
                return null;
            }
            return $this->castToArray($countedNode, $node);
        }
        if ($this->nodeTypeResolver->isNullableTypeOfSpecificType($countedNode, ArrayType::class)) {
            return $this->castToArray($countedNode, $node);
        }
        $countedType = $this->getType($countedNode);
        if ($this->isAlwaysIterableType($countedType)) {
            return null;
        }
        if ($this->nodeTypeResolver->isNullableType($countedNode) || $countedType instanceof NullType) {
            $identical = new Identical($countedNode, $this->nodeFactory->createNull());
            return new Ternary($identical, new LNumber(0), $node);
        }
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::IS_COUNTABLE)) {
            $conditionNode = new FuncCall(new Name('is_countable'), [new Arg($countedNode)]);
        } else {
            $instanceof = new Instanceof_($countedNode, new FullyQualified('Countable'));
            $conditionNode = new BooleanOr($this->nodeFactory->createFuncCall('is_array', [new Arg($countedNode)]), $instanceof);
        }
        return new Ternary($conditionNode, $node, new LNumber(0));
    }
    private function isAlwaysIterableType(Type $possibleUnionType) : bool
    {
        if ($possibleUnionType->isIterable()->yes()) {
            return \true;
        }
        if (!$possibleUnionType instanceof UnionType) {
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
    private function shouldSkip(FuncCall $funcCall) : bool
    {
        if (!$this->isName($funcCall, 'count')) {
            return \true;
        }
        if (!isset($funcCall->args[0])) {
            return \true;
        }
        if (!$funcCall->args[0] instanceof Arg) {
            return \true;
        }
        if ($funcCall->args[0]->value instanceof ClassConstFetch) {
            return \true;
        }
        $parentNode = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Ternary) {
            return \true;
        }
        // skip node in trait, as impossible to analyse
        $trait = $this->betterNodeFinder->findParentType($funcCall, Trait_::class);
        if ($trait instanceof Trait_) {
            return \true;
        }
        if (!$funcCall->args[0]->value instanceof Variable) {
            return \false;
        }
        $parentNode = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Node) {
            $originalParentNode = $parentNode->getAttribute(AttributeKey::ORIGINAL_NODE);
            if (!$this->nodeComparator->areNodesEqual($parentNode, $originalParentNode)) {
                return \true;
            }
        }
        return $this->variableAnalyzer->isStaticOrGlobal($funcCall->args[0]->value);
    }
    private function castToArray(Expr $countedExpr, FuncCall $funcCall) : FuncCall
    {
        $castArray = new Array_($countedExpr);
        $funcCall->args = [new Arg($castArray)];
        return $funcCall;
    }
}
