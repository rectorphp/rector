<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast\String_ as CastString_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ErrorType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php73\NodeTypeAnalyzer\NodeTypeAnalyzer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector\NullToStrictStringFuncCallArgRectorTest
 */
final class NullToStrictStringFuncCallArgRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var array<string, string[]>
     */
    private const ARG_POSITION_NAME_NULL_TO_STRICT_STRING = ['preg_split' => ['subject'], 'preg_match' => ['subject'], 'preg_match_all' => ['subject'], 'explode' => ['string'], 'strlen' => ['string'], 'str_contains' => ['haystack', 'needle'], 'strtotime' => ['datetime'], 'str_replace' => ['subject'], 'substr' => ['string'], 'str_starts_with' => ['haystack', 'needle'], 'strtoupper' => ['string'], 'strtolower' => ['string'], 'strpos' => ['haystack', 'needle'], 'stripos' => ['haystack', 'needle']];
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    /**
     * @readonly
     * @var \Rector\Php73\NodeTypeAnalyzer\NodeTypeAnalyzer
     */
    private $nodeTypeAnalyzer;
    public function __construct(ReflectionResolver $reflectionResolver, ArgsAnalyzer $argsAnalyzer, NodeTypeAnalyzer $nodeTypeAnalyzer)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->argsAnalyzer = $argsAnalyzer;
        $this->nodeTypeAnalyzer = $nodeTypeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change null to strict string defined function call args', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        preg_split("#a#", null);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        preg_split("#a#", '');
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
        if ($this->shouldSkip($node)) {
            return null;
        }
        $args = $node->getArgs();
        $positions = $this->argsAnalyzer->hasNamedArg($args) ? $this->resolveNamedPositions($node, $args) : $this->resolveOriginalPositions($node);
        if ($positions === []) {
            return null;
        }
        $isChanged = \false;
        foreach ($positions as $position) {
            $result = $this->processNullToStrictStringOnNodePosition($node, $args, $position);
            if ($result instanceof Node) {
                $node = $result;
                $isChanged = \true;
            }
        }
        if ($isChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_NULL_ARG_IN_STRING_FUNCTION;
    }
    /**
     * @param Arg[] $args
     * @return int[]|string[]
     */
    private function resolveNamedPositions(FuncCall $funcCall, array $args) : array
    {
        $functionName = $this->nodeNameResolver->getName($funcCall);
        $argNames = self::ARG_POSITION_NAME_NULL_TO_STRICT_STRING[$functionName];
        $positions = [];
        foreach ($args as $position => $arg) {
            if (!$arg->name instanceof Identifier) {
                continue;
            }
            if (!$this->nodeNameResolver->isNames($arg->name, $argNames)) {
                continue;
            }
            $positions[] = $position;
        }
        return $positions;
    }
    /**
     * @param Arg[] $args
     * @param int|string $position
     */
    private function processNullToStrictStringOnNodePosition(FuncCall $funcCall, array $args, $position) : ?FuncCall
    {
        $argValue = $args[$position]->value;
        if ($argValue instanceof ConstFetch && $this->valueResolver->isNull($argValue)) {
            $args[$position]->value = new String_('');
            $funcCall->args = $args;
            return $funcCall;
        }
        $type = $this->nodeTypeResolver->getType($argValue);
        if ($this->nodeTypeAnalyzer->isStringyType($type)) {
            return null;
        }
        if ($this->isAnErrorTypeFromParentScope($argValue, $type)) {
            return null;
        }
        if ($args[$position]->value instanceof MethodCall) {
            $trait = $this->betterNodeFinder->findParentType($funcCall, Trait_::class);
            if ($trait instanceof Trait_) {
                return null;
            }
        }
        if ($this->isCastedReassign($argValue)) {
            return null;
        }
        $args[$position]->value = new CastString_($argValue);
        $funcCall->args = $args;
        return $funcCall;
    }
    private function isCastedReassign(Expr $expr) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstPrevious($expr, function (Node $subNode) use($expr) : bool {
            if (!$subNode instanceof Assign) {
                return \false;
            }
            if (!$this->nodeComparator->areNodesEqual($subNode->var, $expr)) {
                return \false;
            }
            return $subNode->expr instanceof CastString_;
        });
    }
    private function isAnErrorTypeFromParentScope(Expr $expr, Type $type) : bool
    {
        if (!$type instanceof MixedType) {
            return \false;
        }
        $scope = $expr->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return \false;
        }
        $parentScope = $scope->getParentScope();
        if ($parentScope instanceof Scope) {
            return $parentScope->getType($expr) instanceof ErrorType;
        }
        return \false;
    }
    /**
     * @return int[]|string[]
     */
    private function resolveOriginalPositions(FuncCall $funcCall) : array
    {
        $functionReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($funcCall);
        if (!$functionReflection instanceof NativeFunctionReflection) {
            return [];
        }
        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($functionReflection->getVariants());
        $functionName = $this->nodeNameResolver->getName($funcCall);
        $argNames = self::ARG_POSITION_NAME_NULL_TO_STRICT_STRING[$functionName];
        $positions = [];
        foreach ($parametersAcceptor->getParameters() as $position => $parameterReflection) {
            if (\in_array($parameterReflection->getName(), $argNames, \true)) {
                $positions[] = $position;
            }
        }
        return $positions;
    }
    private function shouldSkip(FuncCall $funcCall) : bool
    {
        $functionNames = \array_keys(self::ARG_POSITION_NAME_NULL_TO_STRICT_STRING);
        return !$this->nodeNameResolver->isNames($funcCall, $functionNames);
    }
}
