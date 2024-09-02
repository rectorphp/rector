<?php

declare (strict_types=1);
namespace Rector\Php72\NodeFactory;

use RectorPrefix202409\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\UnionType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Parser\InlineCodeParser;
use Rector\PhpParser\Parser\SimplePhpParser;
final class AnonymousFunctionFactory
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\PhpParser\Parser\SimplePhpParser
     */
    private $simplePhpParser;
    /**
     * @readonly
     * @var \Rector\PhpParser\Parser\InlineCodeParser
     */
    private $inlineCodeParser;
    /**
     * @var string
     * @see https://regex101.com/r/jkLLlM/2
     */
    private const DIM_FETCH_REGEX = '#(\\$|\\\\|\\x0)(?<number>\\d+)#';
    public function __construct(NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, SimplePhpParser $simplePhpParser, InlineCodeParser $inlineCodeParser)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->simplePhpParser = $simplePhpParser;
        $this->inlineCodeParser = $inlineCodeParser;
    }
    /**
     * @api
     * @param Param[] $params
     * @param Stmt[] $stmts
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\NullableType|\PhpParser\Node\UnionType|\PhpParser\Node\ComplexType|null $returnTypeNode
     */
    public function create(array $params, array $stmts, $returnTypeNode, bool $static = \false) : Closure
    {
        $useVariables = $this->createUseVariablesFromParams($stmts, $params);
        $anonymousFunctionClosure = new Closure();
        $anonymousFunctionClosure->params = $params;
        if ($static) {
            $anonymousFunctionClosure->static = $static;
        }
        foreach ($useVariables as $useVariable) {
            $anonymousFunctionClosure->uses[] = new ClosureUse($useVariable);
        }
        if ($returnTypeNode instanceof Node) {
            $anonymousFunctionClosure->returnType = $returnTypeNode;
        }
        $anonymousFunctionClosure->stmts = $stmts;
        return $anonymousFunctionClosure;
    }
    public function createAnonymousFunctionFromExpr(Expr $expr) : ?Closure
    {
        $stringValue = $this->inlineCodeParser->stringify($expr);
        $phpCode = '<?php ' . $stringValue . ';';
        $contentStmts = $this->simplePhpParser->parseString($phpCode);
        $anonymousFunction = new Closure();
        $firstNode = $contentStmts[0] ?? null;
        if (!$firstNode instanceof Expression) {
            return null;
        }
        $stmt = $firstNode->expr;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmt, static function (Node $node) : Node {
            if (!$node instanceof String_) {
                return $node;
            }
            $match = Strings::match($node->value, self::DIM_FETCH_REGEX);
            if ($match === null) {
                return $node;
            }
            $matchesVariable = new Variable('matches');
            return new ArrayDimFetch($matchesVariable, new LNumber((int) $match['number']));
        });
        $anonymousFunction->stmts[] = new Return_($stmt);
        $anonymousFunction->params[] = new Param(new Variable('matches'));
        $variables = $expr instanceof Variable ? [] : $this->betterNodeFinder->findInstanceOf($expr, Variable::class);
        $anonymousFunction->uses = \array_map(static function (Variable $variable) : ClosureUse {
            return new ClosureUse($variable);
        }, $variables);
        return $anonymousFunction;
    }
    /**
     * @param Param[] $params
     * @return string[]
     */
    private function collectParamNames(array $params) : array
    {
        $paramNames = [];
        foreach ($params as $param) {
            $paramNames[] = $this->nodeNameResolver->getName($param);
        }
        return $paramNames;
    }
    /**
     * @param Node[] $nodes
     * @param Param[] $params
     * @return array<string, Variable>
     */
    private function createUseVariablesFromParams(array $nodes, array $params) : array
    {
        $paramNames = $this->collectParamNames($params);
        /** @var Variable[] $variables */
        $variables = $this->betterNodeFinder->findInstanceOf($nodes, Variable::class);
        /** @var array<string, Variable> $filteredVariables */
        $filteredVariables = [];
        $alreadyAssignedVariables = [];
        foreach ($variables as $variable) {
            // "$this" is allowed
            if ($this->nodeNameResolver->isName($variable, 'this')) {
                continue;
            }
            $variableName = $this->nodeNameResolver->getName($variable);
            if ($variableName === null) {
                continue;
            }
            if (\in_array($variableName, $paramNames, \true)) {
                continue;
            }
            if ($variable->getAttribute(AttributeKey::IS_BEING_ASSIGNED) === \true || $variable->getAttribute(AttributeKey::IS_PARAM_VAR) === \true || $variable->getAttribute(AttributeKey::IS_VARIABLE_LOOP) === \true) {
                $alreadyAssignedVariables[] = $variableName;
            }
            if (!$this->nodeNameResolver->isNames($variable, $alreadyAssignedVariables)) {
                $filteredVariables[$variableName] = $variable;
            }
        }
        return $filteredVariables;
    }
}
