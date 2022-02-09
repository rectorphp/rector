<?php

declare (strict_types=1);
namespace Rector\Php72\NodeFactory;

use RectorPrefix20220209\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
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
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\UnionType;
use PHPStan\Reflection\FunctionVariantWithPhpDocs;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\VoidType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Parser\SimplePhpParser;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20220209\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class AnonymousFunctionFactory
{
    /**
     * @var string
     * @see https://regex101.com/r/jkLLlM/2
     */
    private const DIM_FETCH_REGEX = '#(\\$|\\\\|\\x0)(?<number>\\d+)#';
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Parser\SimplePhpParser
     */
    private $simplePhpParser;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \RectorPrefix20220209\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\Core\PhpParser\Parser\SimplePhpParser $simplePhpParser, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeFactory = $nodeFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->simplePhpParser = $simplePhpParser;
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @param Param[] $params
     * @param Stmt[] $stmts
     * @param \PhpParser\Node\ComplexType|\PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\NullableType|\PhpParser\Node\UnionType|null $returnTypeNode
     */
    public function create(array $params, array $stmts, $returnTypeNode, bool $static = \false) : \PhpParser\Node\Expr\Closure
    {
        $useVariables = $this->createUseVariablesFromParams($stmts, $params);
        $anonymousFunctionNode = new \PhpParser\Node\Expr\Closure();
        $anonymousFunctionNode->params = $params;
        if ($static) {
            $anonymousFunctionNode->static = $static;
        }
        foreach ($useVariables as $useVariable) {
            $anonymousFunctionNode = $this->applyNestedUses($anonymousFunctionNode, $useVariable);
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
            $returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($functionVariantWithPhpDoc->getReturnType(), \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::RETURN());
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
        $contentStmts = $this->simplePhpParser->parseString($phpCode);
        $anonymousFunction = new \PhpParser\Node\Expr\Closure();
        $firstNode = $contentStmts[0] ?? null;
        if (!$firstNode instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $stmt = $firstNode->expr;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmt, function (\PhpParser\Node $node) : Node {
            if (!$node instanceof \PhpParser\Node\Scalar\String_) {
                return $node;
            }
            $match = \RectorPrefix20220209\Nette\Utils\Strings::match($node->value, self::DIM_FETCH_REGEX);
            if ($match === null) {
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
     * @param ClosureUse[] $uses
     * @return ClosureUse[]
     */
    private function cleanClosureUses(array $uses) : array
    {
        $uniqueUses = [];
        foreach ($uses as $use) {
            if (!\is_string($use->var->name)) {
                continue;
            }
            $variableName = $use->var->name;
            if (\array_key_exists($variableName, $uniqueUses)) {
                continue;
            }
            $uniqueUses[$variableName] = $use;
        }
        return \array_values($uniqueUses);
    }
    private function applyNestedUses(\PhpParser\Node\Expr\Closure $anonymousFunctionNode, \PhpParser\Node\Expr\Variable $useVariable) : \PhpParser\Node\Expr\Closure
    {
        $parent = $this->betterNodeFinder->findParentType($useVariable, \PhpParser\Node\Expr\Closure::class);
        if (!$parent instanceof \PhpParser\Node\Expr\Closure) {
            return $anonymousFunctionNode;
        }
        $paramNames = $this->nodeNameResolver->getNames($parent->params);
        if ($this->nodeNameResolver->isNames($useVariable, $paramNames)) {
            return $anonymousFunctionNode;
        }
        $anonymousFunctionNode = clone $anonymousFunctionNode;
        while ($parent instanceof \PhpParser\Node\Expr\Closure) {
            $parentOfParent = $this->betterNodeFinder->findParentType($parent, \PhpParser\Node\Expr\Closure::class);
            $uses = [];
            while ($parentOfParent instanceof \PhpParser\Node\Expr\Closure) {
                $uses = $this->collectUsesEqual($parentOfParent, $uses, $useVariable);
                $parentOfParent = $this->betterNodeFinder->findParentType($parentOfParent, \PhpParser\Node\Expr\Closure::class);
            }
            $uses = \array_merge($parent->uses, $uses);
            $uses = $this->cleanClosureUses($uses);
            $parent->uses = $uses;
            $parent = $this->betterNodeFinder->findParentType($parent, \PhpParser\Node\Expr\Closure::class);
        }
        return $anonymousFunctionNode;
    }
    /**
     * @param ClosureUse[] $uses
     * @return ClosureUse[]
     */
    private function collectUsesEqual(\PhpParser\Node\Expr\Closure $closure, array $uses, \PhpParser\Node\Expr\Variable $useVariable) : array
    {
        foreach ($closure->params as $param) {
            if ($this->nodeComparator->areNodesEqual($param->var, $useVariable)) {
                $uses[] = new \PhpParser\Node\Expr\ClosureUse($param->var);
            }
        }
        return $uses;
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
        $variableNodes = $this->betterNodeFinder->findInstanceOf($nodes, \PhpParser\Node\Expr\Variable::class);
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
            if ($parentNode instanceof \PhpParser\Node\Expr\Assign || $parentNode instanceof \PhpParser\Node\Stmt\Foreach_ || $parentNode instanceof \PhpParser\Node\Param) {
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
     * @param ParameterReflection[] $parameterReflections
     * @return Param[]
     */
    private function createParams(array $parameterReflections) : array
    {
        $params = [];
        foreach ($parameterReflections as $parameterReflection) {
            $param = new \PhpParser\Node\Param(new \PhpParser\Node\Expr\Variable($parameterReflection->getName()));
            if (!$parameterReflection->getType() instanceof \PHPStan\Type\MixedType) {
                $param->type = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($parameterReflection->getType(), \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PARAM());
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
     * @return \PhpParser\Node\Expr|\PhpParser\Node\Name|\PhpParser\Node\Name\FullyQualified|null
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
        $name = new \PhpParser\Node\Name($className);
        if ($name->isSpecialClassName()) {
            return $name;
        }
        return new \PhpParser\Node\Name\FullyQualified($className);
    }
    /**
     * @return \PhpParser\Node\Expr|\PhpParser\Node\Expr\New_|null
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
