<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PHPStan\Type\ObjectType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\ValueObject\AddClosureParamTypeFromObject;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202506\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\AddClosureParamTypeFromObjectRector\AddClosureParamTypeFromObjectRectorTest
 */
final class AddClosureParamTypeFromObjectRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     */
    private TypeComparator $typeComparator;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @var int
     */
    private const DEFAULT_CLOSURE_ARG_POSITION = 0;
    /**
     * @var AddClosureParamTypeFromObject[]
     */
    private array $addClosureParamTypeFromObjects = [];
    public function __construct(TypeComparator $typeComparator, StaticTypeMapper $staticTypeMapper)
    {
        $this->typeComparator = $typeComparator;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add closure param type based on the object of the method call', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$request = new Request();
$request->when(true, function ($request) {});
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$request = new Request();
$request->when(true, function (Request $request) {});
CODE_SAMPLE
, [new AddClosureParamTypeFromObject('Request', 'when', 1, 0)])]);
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
        foreach ($this->addClosureParamTypeFromObjects as $addClosureParamTypeFromObject) {
            if ($node instanceof MethodCall) {
                $caller = $node->var;
            } elseif ($node instanceof StaticCall) {
                $caller = $node->class;
            } else {
                continue;
            }
            if (!$this->isCallMatch($caller, $addClosureParamTypeFromObject, $node)) {
                continue;
            }
            $type = $this->getType($caller);
            if (!$type instanceof ObjectType) {
                continue;
            }
            return $this->processCallLike($node, $addClosureParamTypeFromObject, $type);
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, AddClosureParamTypeFromObject::class);
        $this->addClosureParamTypeFromObjects = $configuration;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $callLike
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null
     */
    private function processCallLike($callLike, AddClosureParamTypeFromObject $addClosureParamTypeFromArg, ObjectType $objectType)
    {
        if ($callLike->isFirstClassCallable()) {
            return null;
        }
        $callLikeArg = $callLike->args[$addClosureParamTypeFromArg->getCallLikePosition()] ?? null;
        if (!$callLikeArg instanceof Arg) {
            return null;
        }
        // int positions shouldn't have names
        if ($callLikeArg->name instanceof Identifier) {
            return null;
        }
        $functionLike = $callLikeArg->value;
        if (!$functionLike instanceof Closure && !$functionLike instanceof ArrowFunction) {
            return null;
        }
        if (!isset($functionLike->params[$addClosureParamTypeFromArg->getFunctionLikePosition()])) {
            return null;
        }
        $callLikeArg = $callLike->getArgs()[self::DEFAULT_CLOSURE_ARG_POSITION] ?? null;
        if (!$callLikeArg instanceof Arg) {
            return null;
        }
        $hasChanged = $this->refactorParameter($functionLike->params[$addClosureParamTypeFromArg->getFunctionLikePosition()], $objectType);
        if ($hasChanged) {
            return $callLike;
        }
        return null;
    }
    private function refactorParameter(Param $param, ObjectType $objectType) : bool
    {
        // already set → no change
        if ($param->type instanceof Node) {
            $currentParamType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
            if ($this->typeComparator->areTypesEqual($currentParamType, $objectType)) {
                return \false;
            }
        }
        $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($objectType, TypeKind::PARAM);
        $param->type = $paramTypeNode;
        return \true;
    }
    /**
     * @param \PhpParser\Node\Name|\PhpParser\Node\Expr $name
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $call
     */
    private function isCallMatch($name, AddClosureParamTypeFromObject $addClosureParamTypeFromArg, $call) : bool
    {
        if (!$this->isObjectType($name, $addClosureParamTypeFromArg->getObjectType())) {
            return \false;
        }
        return $this->isName($call->name, $addClosureParamTypeFromArg->getMethodName());
    }
}
