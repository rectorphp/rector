<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeTraverser;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeCombinator;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\NodeAnalyzer\ArgsAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamArrayDocblockBasedOnCallableNativeFuncCallRector\AddParamArrayDocblockBasedOnCallableNativeFuncCallRectorTest
 */
final class AddParamArrayDocblockBasedOnCallableNativeFuncCallRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var array<string, array<string, int>>
     */
    private const NATIVE_FUNC_CALLS_WITH_POSITION = ['array_walk' => ['array' => 0, 'callback' => 1], 'array_map' => ['array' => 1, 'callback' => 0], 'usort' => ['array' => 0, 'callback' => 1], 'array_filter' => ['array' => 0, 'callback' => 1]];
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ArgsAnalyzer $argsAnalyzer, PhpDocTypeChanger $phpDocTypeChanger, StaticTypeMapper $staticTypeMapper)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->argsAnalyzer = $argsAnalyzer;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add param array docblock based on callable native function call', [new CodeSample(<<<'CODE_SAMPLE'
function process(array $items): void
{
	array_walk($items, function (stdClass $item) {
		echo $item->value;
    });
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/**
 * @param stdClass[] $items
 */
function process(array $items): void
{
	array_walk($items, function (stdClass $item) {
		echo $item->value;
    });
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     * @return null|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_
     */
    public function refactor(Node $node)
    {
        if ($node->params === []) {
            return null;
        }
        if ($node->stmts === null) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $variableNamesWithArrayType = $this->collectVariableNamesWithArrayType($node, $phpDocInfo);
        if ($variableNamesWithArrayType === []) {
            return null;
        }
        $paramsWithType = [];
        $this->traverseNodesWithCallable($node->stmts, function (Node $subNode) use($variableNamesWithArrayType, $node, &$paramsWithType) : ?int {
            if ($subNode instanceof Class_ || $subNode instanceof Function_) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$subNode instanceof FuncCall) {
                return null;
            }
            if (!$this->isNames($subNode, \array_keys(self::NATIVE_FUNC_CALLS_WITH_POSITION))) {
                return null;
            }
            if ($subNode->isFirstClassCallable()) {
                return null;
            }
            $args = $subNode->getArgs();
            if ($this->argsAnalyzer->hasNamedArg($args)) {
                return null;
            }
            if (\count($args) < 2) {
                return null;
            }
            $funcCallName = (string) $this->getName($subNode);
            $arrayArgValue = $args[self::NATIVE_FUNC_CALLS_WITH_POSITION[$funcCallName]['array']]->value;
            if (!$arrayArgValue instanceof Variable) {
                return null;
            }
            // defined on param provided
            if (!$this->isNames($arrayArgValue, $variableNamesWithArrayType)) {
                return null;
            }
            $arrayArgValueType = $this->nodeTypeResolver->getNativeType($arrayArgValue);
            // type changed, eg: by reassign
            if (!$arrayArgValueType->isArray()->yes()) {
                return null;
            }
            $callbackArgValue = $args[self::NATIVE_FUNC_CALLS_WITH_POSITION[$funcCallName]['callback']]->value;
            if (!$callbackArgValue instanceof ArrowFunction && !$callbackArgValue instanceof Closure) {
                return null;
            }
            // no params or more than 2 params
            if ($callbackArgValue->params === [] || \count($callbackArgValue->params) > 2) {
                return null;
            }
            foreach ($callbackArgValue->params as $callbackArgValueParam) {
                // not typed
                if (!$callbackArgValueParam->type instanceof Node) {
                    return null;
                }
            }
            if (isset($callbackArgValue->params[1]) && !$this->nodeComparator->areNodesEqual($callbackArgValue->params[0]->type, $callbackArgValue->params[1]->type)) {
                return null;
            }
            if (!$callbackArgValue->params[0]->type instanceof Node) {
                return null;
            }
            $arrayArgValueName = (string) $this->getName($arrayArgValue);
            $paramToUpdate = $this->getParamByName($node, $arrayArgValueName);
            if (!$paramToUpdate instanceof Param) {
                return null;
            }
            $paramType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($callbackArgValue->params[0]->type);
            if ($paramType instanceof MixedType) {
                return null;
            }
            $paramsWithType[$this->getName($paramToUpdate)] = \array_unique(\array_merge($paramsWithType[$this->getName($paramToUpdate)] ?? [], [$paramType]), \SORT_REGULAR);
            return null;
        });
        $hasChanged = \false;
        foreach ($paramsWithType as $paramName => $type) {
            $type = \count($type) > 1 ? TypeCombinator::union(...$type) : \current($type);
            /** @var Param $paramByName */
            $paramByName = $this->getParamByName($node, $paramName);
            $this->phpDocTypeChanger->changeParamType($node, $phpDocInfo, new ArrayType(new MixedType(), $type), $paramByName, $paramName);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     */
    private function getParamByName($node, string $paramName) : ?Param
    {
        foreach ($node->params as $param) {
            if ($this->isName($param, $paramName)) {
                return $param;
            }
        }
        return null;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     */
    private function collectVariableNamesWithArrayType($node, PhpDocInfo $phpDocInfo) : array
    {
        $variableNamesWithArrayType = [];
        foreach ($node->params as $param) {
            if (!$param->type instanceof Identifier) {
                continue;
            }
            if ($param->type->toString() !== 'array') {
                continue;
            }
            if (!$param->var instanceof Variable) {
                continue;
            }
            $paramName = $this->getName($param);
            $paramTag = $phpDocInfo->getParamTagValueByName($paramName);
            if ($paramTag instanceof ParamTagValueNode) {
                continue;
            }
            $variableNamesWithArrayType[] = $paramName;
        }
        return $variableNamesWithArrayType;
    }
}
