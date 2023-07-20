<?php

declare (strict_types=1);
namespace Rector\Php71\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\Cast\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\NodeTraverser;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\NodeAnalyzer\VariableAnalyzer;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractScopeAwareRector;
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
final class CountOnNullRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
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
        return [FuncCall::class, Ternary::class];
    }
    /**
     * @param FuncCall|Ternary $node
     * @return int|\PhpParser\Node\Expr\Ternary|null|\PhpParser\Node\Expr\FuncCall
     */
    public function refactorWithScope(Node $node, Scope $scope)
    {
        if ($this->isInsideTrait($scope)) {
            return null;
        }
        if ($node instanceof Ternary) {
            if ($this->shouldSkipTernaryIfElseCountFuncCall($node)) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
            return null;
        }
        if ($this->shouldSkipFuncCall($node)) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        $countedExpr = $firstArg->value;
        if ($this->countableTypeAnalyzer->isCountableType($countedExpr)) {
            return null;
        }
        // this can lead to false positive by PHPStan
        $onlyValueType = $this->getType($countedExpr);
        if ($onlyValueType instanceof ArrayType) {
            return $this->refactorArrayType($countedExpr, $onlyValueType, $scope, $node);
        }
        if ($this->nodeTypeResolver->isNullableTypeOfSpecificType($countedExpr, ArrayType::class)) {
            return $this->castToArray($countedExpr, $node);
        }
        if ($this->isAlwaysIterableType($onlyValueType)) {
            return null;
        }
        if ($this->nodeTypeResolver->isNullableType($countedExpr) || $onlyValueType instanceof NullType) {
            $identical = new Identical($countedExpr, $this->nodeFactory->createNull());
            return new Ternary($identical, new LNumber(0), $node);
        }
        $conditionExpr = $this->createConditionExpr($countedExpr);
        return new Ternary($conditionExpr, $node, new LNumber(0));
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
    private function shouldSkipFuncCall(FuncCall $funcCall) : bool
    {
        if (!$this->isName($funcCall, 'count')) {
            return \true;
        }
        if ($funcCall->isFirstClassCallable()) {
            return \true;
        }
        $firstArg = $funcCall->getArgs()[0];
        // just added node, lets skip it to be sure we're not using mixing
        $origNode = $firstArg->value->getAttribute(AttributeKey::ORIGINAL_NODE);
        if (!$origNode instanceof Node) {
            return \true;
        }
        if (!$firstArg->value instanceof Variable) {
            return \false;
        }
        return $this->variableAnalyzer->isStaticOrGlobal($firstArg->value);
    }
    private function castToArray(Expr $countedExpr, FuncCall $funcCall) : FuncCall
    {
        $castArray = new Array_($countedExpr);
        $funcCall->args = [new Arg($castArray)];
        return $funcCall;
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\BooleanOr|\PhpParser\Node\Expr\FuncCall
     */
    private function createConditionExpr(Expr $countedExpr)
    {
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::IS_COUNTABLE)) {
            return new FuncCall(new Name('is_countable'), [new Arg($countedExpr)]);
        }
        $instanceof = new Instanceof_($countedExpr, new FullyQualified('Countable'));
        $isArrayFuncCall = $this->nodeFactory->createFuncCall('is_array', [new Arg($countedExpr)]);
        return new BooleanOr($isArrayFuncCall, $instanceof);
    }
    /**
     * PHPStan is unable to update type based on ternary check, so excluded null is ignored, @see https://github.com/rectorphp/rector-src/pull/3924#discussion_r1200415462
     */
    private function shouldSkipTernaryIfElseCountFuncCall(Ternary $ternary) : bool
    {
        if ($ternary->if instanceof FuncCall && $this->isName($ternary->if, 'count')) {
            return \true;
        }
        return $ternary->else instanceof FuncCall && $this->isName($ternary->else, 'count');
    }
    private function refactorArrayType(Expr $countedExpr, ArrayType $arrayType, Scope $scope, FuncCall $funcCall) : ?FuncCall
    {
        if (!$this->countableAnalyzer->isCastableArrayType($countedExpr, $arrayType, $scope)) {
            return null;
        }
        return $this->castToArray($countedExpr, $funcCall);
    }
    private function isInsideTrait(Scope $scope) : bool
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $classReflection->isTrait();
    }
}
