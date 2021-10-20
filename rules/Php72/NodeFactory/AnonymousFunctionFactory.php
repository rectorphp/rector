<?php

declare (strict_types=1);
namespace Rector\Php72\NodeFactory;

use RectorPrefix20211020\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\UnionType;
use PhpParser\Parser;
use PHPStan\Reflection\FunctionVariantWithPhpDocs;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\VoidType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class AnonymousFunctionFactory
{
    /**
     * @var string
     * @see https://regex101.com/r/jkLLlM/2
     */
    private const DIM_FETCH_REGEX = '#(\\$|\\\\|\\x0)(?<number>\\d+)#';
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var \PhpParser\Parser
     */
    private $parser;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \PhpParser\Parser $parser)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeFactory = $nodeFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->parser = $parser;
    }
    /**
     * @param Param[] $params
     * @param Stmt[] $stmts
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\NullableType|\PhpParser\Node\UnionType|\PhpParser\Node\ComplexType|null $returnTypeNode
     */
    public function create(array $params, array $stmts, $returnTypeNode) : \PhpParser\Node\Expr\Closure
    {
        $useVariables = $this->createUseVariablesFromParams($stmts, $params);
        $anonymousFunctionNode = new \PhpParser\Node\Expr\Closure();
        $anonymousFunctionNode->params = $params;
        foreach ($useVariables as $useVariable) {
            $anonymousFunctionNode->uses[] = new \PhpParser\Node\Expr\ClosureUse($useVariable);
        }
        if ($returnTypeNode instanceof \PhpParser\Node) {
            $anonymousFunctionNode->returnType = $returnTypeNode;
        }
        $anonymousFunctionNode->stmts = $stmts;
        return $anonymousFunctionNode;
    }
    public function createFromPhpMethodReflection(\PHPStan\Reflection\Php\PhpMethodReflection $phpMethodReflection, \PhpParser\Node\Expr $expr) : ?\PhpParser\Node\Expr\Closure
    {
        /** @var FunctionVariantWithPhpDocs $functionVariantWithPhpDoc */
        $functionVariantWithPhpDoc = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($phpMethodReflection->getVariants());
        $anonymousFunction = new \PhpParser\Node\Expr\Closure();
        $newParams = $this->createParams($functionVariantWithPhpDoc->getParameters());
        $anonymousFunction->params = $newParams;
        $innerMethodCall = $this->createInnerMethodCall($phpMethodReflection, $expr, $newParams);
        if ($innerMethodCall === null) {
            return null;
        }
        if (!$functionVariantWithPhpDoc->getReturnType() instanceof \PHPStan\Type\MixedType) {
            $returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($functionVariantWithPhpDoc->getReturnType(), \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind::RETURN());
            $anonymousFunction->returnType = $returnType;
        }
        // does method return something?
        if (!$functionVariantWithPhpDoc->getReturnType() instanceof \PHPStan\Type\VoidType) {
            $anonymousFunction->stmts[] = new \PhpParser\Node\Stmt\Return_($innerMethodCall);
        } else {
            $anonymousFunction->stmts[] = new \PhpParser\Node\Stmt\Expression($innerMethodCall);
        }
        if ($expr instanceof \PhpParser\Node\Expr\Variable && !$this->nodeNameResolver->isName($expr, 'this')) {
            $anonymousFunction->uses[] = new \PhpParser\Node\Expr\ClosureUse($expr);
        }
        return $anonymousFunction;
    }
    public function createAnonymousFunctionFromString(\PhpParser\Node\Expr $expr) : ?\PhpParser\Node\Expr\Closure
    {
        if (!$expr instanceof \PhpParser\Node\Scalar\String_) {
            // not supported yet
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $phpCode = '<?php ' . $expr->value . ';';
        $contentNodes = (array) $this->parser->parse($phpCode);
        $anonymousFunction = new \PhpParser\Node\Expr\Closure();
        $firstNode = $contentNodes[0] ?? null;
        if (!$firstNode instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $stmt = $firstNode->expr;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmt, function (\PhpParser\Node $node) : Node {
            if (!$node instanceof \PhpParser\Node\Scalar\String_) {
                return $node;
            }
            $match = \RectorPrefix20211020\Nette\Utils\Strings::match($node->value, self::DIM_FETCH_REGEX);
            if (!$match) {
                return $node;
            }
            $matchesVariable = new \PhpParser\Node\Expr\Variable('matches');
            return new \PhpParser\Node\Expr\ArrayDimFetch($matchesVariable, new \PhpParser\Node\Scalar\LNumber((int) $match['number']));
        });
        $anonymousFunction->stmts[] = new \PhpParser\Node\Stmt\Return_($stmt);
        $anonymousFunction->params[] = new \PhpParser\Node\Param(new \PhpParser\Node\Expr\Variable('matches'));
        return $anonymousFunction;
    }
    /**
     * @param Node[] $nodes
     * @param Param[] $paramNodes
     * @return Variable[]
     */
    private function createUseVariablesFromParams(array $nodes, array $paramNodes) : array
    {
        $paramNames = [];
        foreach ($paramNodes as $paramNode) {
            $paramNames[] = $this->nodeNameResolver->getName($paramNode);
        }
        $variableNodes = $this->resolveVariableNodes($nodes);
        /** @var Variable[] $filteredVariables */
        $filteredVariables = [];
        $alreadyAssignedVariables = [];
        foreach ($variableNodes as $variableNode) {
            // "$this" is allowed
            if ($this->nodeNameResolver->isName($variableNode, 'this')) {
                continue;
            }
            $variableName = $this->nodeNameResolver->getName($variableNode);
            if ($variableName === null) {
                continue;
            }
            if (\in_array($variableName, $paramNames, \true)) {
                continue;
            }
            $parentNode = $variableNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if ($parentNode instanceof \PhpParser\Node\Expr\Assign) {
                $alreadyAssignedVariables[] = $variableName;
            }
            if ($this->nodeNameResolver->isNames($variableNode, $alreadyAssignedVariables)) {
                continue;
            }
            $filteredVariables[$variableName] = $variableNode;
        }
        return $filteredVariables;
    }
    /**
     * @param Node[] $nodes
     * @return Variable[]
     */
    private function resolveVariableNodes(array $nodes) : array
    {
        return $this->betterNodeFinder->find($nodes, function (\PhpParser\Node $subNode) : bool {
            if (!$subNode instanceof \PhpParser\Node\Expr\Variable) {
                return \false;
            }
            $parentArrowFunction = $this->betterNodeFinder->findParentType($subNode, \PhpParser\Node\Expr\ArrowFunction::class);
            if ($parentArrowFunction instanceof \PhpParser\Node\Expr\ArrowFunction) {
                return !(bool) $this->betterNodeFinder->findParentType($parentArrowFunction, \PhpParser\Node\Expr\ArrowFunction::class);
            }
            return \true;
        });
    }
    /**
     * @param ParameterReflection[] $parameterReflections
     * @return Param[]
     */
    private function createParams(array $parameterReflections) : array
    {
        $params = [];
        foreach ($parameterReflections as $parameterReflection) {
            $param = new \PhpParser\Node\Param(new \PhpParser\Node\Expr\Variable($parameterReflection->getName()));
            if (!$parameterReflection->getType() instanceof \PHPStan\Type\MixedType) {
                $param->type = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($parameterReflection->getType(), \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind::PARAM());
            }
            $params[] = $param;
        }
        return $params;
    }
    /**
     * @param Param[] $params
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null
     */
    private function createInnerMethodCall(\PHPStan\Reflection\Php\PhpMethodReflection $phpMethodReflection, \PhpParser\Node\Expr $expr, array $params)
    {
        if ($phpMethodReflection->isStatic()) {
            $expr = $this->normalizeClassConstFetchForStatic($expr);
            if ($expr === null) {
                return null;
            }
            $innerMethodCall = new \PhpParser\Node\Expr\StaticCall($expr, $phpMethodReflection->getName());
        } else {
            $expr = $this->resolveExpr($expr);
            if (!$expr instanceof \PhpParser\Node\Expr) {
                return null;
            }
            $innerMethodCall = new \PhpParser\Node\Expr\MethodCall($expr, $phpMethodReflection->getName());
        }
        $innerMethodCall->args = $this->nodeFactory->createArgsFromParams($params);
        return $innerMethodCall;
    }
    /**
     * @return null|\PhpParser\Node\Name\FullyQualified|\PhpParser\Node\Expr
     */
    private function normalizeClassConstFetchForStatic(\PhpParser\Node\Expr $expr)
    {
        if (!$expr instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return $expr;
        }
        if (!$this->nodeNameResolver->isName($expr->name, 'class')) {
            return $expr;
        }
        // dynamic name, nothing we can do
        $className = $this->nodeNameResolver->getName($expr->class);
        if ($className === null) {
            return null;
        }
        return new \PhpParser\Node\Name\FullyQualified($className);
    }
    /**
     * @return \PhpParser\Node\Expr\New_|\PhpParser\Node\Expr|null
     */
    private function resolveExpr(\PhpParser\Node\Expr $expr)
    {
        if (!$expr instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return $expr;
        }
        if (!$this->nodeNameResolver->isName($expr->name, 'class')) {
            return $expr;
        }
        // dynamic name, nothing we can do
        $className = $this->nodeNameResolver->getName($expr->class);
        if ($className === null) {
            return null;
        }
        return new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name\FullyQualified($className));
    }
}
