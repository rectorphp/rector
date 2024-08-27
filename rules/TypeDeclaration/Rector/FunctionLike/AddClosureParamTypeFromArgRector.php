<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\ValueObject\AddClosureParamTypeFromArg;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202408\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\AddClosureParamTypeFromArgRector\AddClosureParamTypeFromArgRectorTest
 */
final class AddClosureParamTypeFromArgRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var AddClosureParamTypeFromArg[]
     */
    private $addClosureParamTypeFromArgs = [];
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function __construct(TypeComparator $typeComparator, StaticTypeMapper $staticTypeMapper, ReflectionProvider $reflectionProvider)
    {
        $this->typeComparator = $typeComparator;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add param types where needed', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$app->extend(SomeClass::class, function ($parameter) {});
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$app->extend(SomeClass::class, function (SomeClass $parameter) {});
CODE_SAMPLE
, [new AddClosureParamTypeFromArg('SomeClass', 'extend', 1, 0, 0)])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        $this->hasChanged = \false;
        foreach ($this->addClosureParamTypeFromArgs as $addClosureParamTypeFromArg) {
            if ($node instanceof MethodCall) {
                $caller = $node->var;
            } elseif ($node instanceof StaticCall) {
                $caller = $node->class;
            } else {
                continue;
            }
            if (!$this->isObjectType($caller, $addClosureParamTypeFromArg->getObjectType())) {
                continue;
            }
            if (!$node->name instanceof Identifier) {
                continue;
            }
            if (!$this->isName($node->name, $addClosureParamTypeFromArg->getMethodName())) {
                continue;
            }
            $this->processCallLike($node, $addClosureParamTypeFromArg);
        }
        if (!$this->hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, AddClosureParamTypeFromArg::class);
        $this->addClosureParamTypeFromArgs = $configuration;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $callLike
     */
    private function processCallLike($callLike, AddClosureParamTypeFromArg $addClosureParamTypeFromArg) : void
    {
        if ($callLike->isFirstClassCallable()) {
            return;
        }
        if ($callLike->getArgs() === []) {
            return;
        }
        $arg = $callLike->args[$addClosureParamTypeFromArg->getCallLikePosition()] ?? null;
        if (!$arg instanceof Arg) {
            return;
        }
        // int positions shouldn't have names
        if ($arg->name instanceof Identifier) {
            return;
        }
        $functionLike = $arg->value;
        if (!$functionLike instanceof Closure && !$functionLike instanceof ArrowFunction) {
            return;
        }
        if (!isset($functionLike->params[$addClosureParamTypeFromArg->getFunctionLikePosition()])) {
            return;
        }
        if (!($arg = $this->getArg($addClosureParamTypeFromArg->getFromArgPosition(), $callLike->getArgs())) instanceof Arg) {
            return;
        }
        $this->refactorParameter($functionLike->params[$addClosureParamTypeFromArg->getFunctionLikePosition()], $arg);
    }
    /**
     * @param Arg[] $args
     */
    private function getArg(int $position, array $args) : ?Arg
    {
        return $args[$position] ?? null;
    }
    private function refactorParameter(Param $param, Arg $arg) : void
    {
        $argType = $this->nodeTypeResolver->getType($arg->value);
        if ($argType instanceof GenericClassStringType) {
            $closureType = $argType->getGenericType();
        } elseif ($argType instanceof ConstantStringType) {
            if ($this->reflectionProvider->hasClass($argType->getValue())) {
                $closureType = new ObjectType($argType->getValue());
            } else {
                $closureType = new StringType();
            }
        } else {
            return;
        }
        // already set → no change
        if ($param->type !== null) {
            $currentParamType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
            if ($this->typeComparator->areTypesEqual($currentParamType, $closureType)) {
                return;
            }
        }
        $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($closureType, TypeKind::PARAM);
        $this->hasChanged = \true;
        $param->type = $paramTypeNode;
    }
}
