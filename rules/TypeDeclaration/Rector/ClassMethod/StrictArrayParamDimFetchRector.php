<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp\Coalesce as AssignOpCoalesce;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\Cast\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeTraverser;
use Rector\Rector\AbstractRector;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\StrictArrayParamDimFetchRector\StrictArrayParamDimFetchRectorTest
 */
final class StrictArrayParamDimFetchRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard
     */
    private $parentClassMethodTypeOverrideGuard;
    public function __construct(ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard)
    {
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add array type based on array dim fetch use', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function resolve($item)
    {
        return $item['name'];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function resolve(array $item)
    {
        return $item['name'];
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
        return [ClassMethod::class, Function_::class, Closure::class];
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        if ($node instanceof ClassMethod && $this->parentClassMethodTypeOverrideGuard->hasParentClassMethod($node)) {
            return null;
        }
        foreach ($node->getParams() as $param) {
            if ($param->type instanceof Node) {
                continue;
            }
            if ($param->variadic) {
                continue;
            }
            if ($param->default instanceof Expr && !$this->getType($param->default)->isArray()->yes()) {
                continue;
            }
            if (!$this->isParamAccessedArrayDimFetch($param, $node)) {
                continue;
            }
            $param->type = new Identifier('array');
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function isParamAccessedArrayDimFetch(Param $param, $functionLike) : bool
    {
        if ($functionLike->stmts === null) {
            return \false;
        }
        $paramName = $this->getName($param);
        $isParamAccessedArrayDimFetch = \false;
        $this->traverseNodesWithCallable($functionLike->stmts, function (Node $node) use($param, $paramName, &$isParamAccessedArrayDimFetch) : ?int {
            if ($node instanceof Class_ || $node instanceof FunctionLike) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($this->shouldStop($node, $param, $paramName)) {
                // force set to false to avoid too early replaced
                $isParamAccessedArrayDimFetch = \false;
                return NodeTraverser::STOP_TRAVERSAL;
            }
            if (!$node instanceof ArrayDimFetch) {
                return null;
            }
            if (!$node->dim instanceof Expr) {
                return null;
            }
            if (!$node->var instanceof Variable) {
                return null;
            }
            if (!$this->isName($node->var, $paramName)) {
                return null;
            }
            // skip possible strings
            $variableType = $this->getType($node->var);
            if ($variableType->isString()->yes()) {
                // force set to false to avoid too early replaced
                $isParamAccessedArrayDimFetch = \false;
                return NodeTraverser::STOP_TRAVERSAL;
            }
            // skip integer in possibly string type as string can be accessed via int
            $dimType = $this->getType($node->dim);
            if ($dimType->isInteger()->yes() && $variableType->isString()->maybe()) {
                return null;
            }
            $isParamAccessedArrayDimFetch = \true;
            return null;
        });
        return $isParamAccessedArrayDimFetch;
    }
    private function isEchoed(Node $node, string $paramName) : bool
    {
        if (!$node instanceof Echo_) {
            return \false;
        }
        foreach ($node->exprs as $expr) {
            if ($expr instanceof Variable && $this->isName($expr, $paramName)) {
                return \true;
            }
        }
        return \false;
    }
    private function shouldStop(Node $node, Param $param, string $paramName) : bool
    {
        $nodeToCheck = null;
        if (!$param->default instanceof Expr) {
            if ($node instanceof Isset_) {
                foreach ($node->vars as $var) {
                    if ($var instanceof ArrayDimFetch && $var->var instanceof Variable && $var->var->name === $paramName) {
                        return \true;
                    }
                }
            }
            if ($node instanceof Empty_ && $node->expr instanceof ArrayDimFetch && $node->expr->var instanceof Variable && $node->expr->var->name === $paramName) {
                return \true;
            }
        }
        if ($node instanceof FuncCall && !$node->isFirstClassCallable() && $this->isNames($node, ['is_array', 'is_string', 'is_int', 'is_bool', 'is_float'])) {
            $firstArg = $node->getArgs()[0];
            $nodeToCheck = $firstArg->value;
        }
        if ($node instanceof Expression) {
            $nodeToCheck = $node->expr;
        }
        if ($node instanceof Coalesce) {
            $nodeToCheck = $node->left;
        }
        if ($node instanceof AssignOpCoalesce) {
            $nodeToCheck = $node->var;
        }
        if ($this->isMethodCallOrArrayDimFetch($paramName, $nodeToCheck)) {
            return \true;
        }
        if ($nodeToCheck instanceof Variable && $this->isName($nodeToCheck, $paramName)) {
            return \true;
        }
        if ($this->isEmptyOrEchoedOrCasted($node, $paramName)) {
            return \true;
        }
        return $this->isReassignAndUseAsArg($node, $paramName);
    }
    private function isReassignAndUseAsArg(Node $node, string $paramName) : bool
    {
        if (!$node instanceof Assign) {
            return \false;
        }
        if (!$node->var instanceof Variable) {
            return \false;
        }
        if (!$this->isName($node->var, $paramName)) {
            return \false;
        }
        if (!$node->expr instanceof CallLike) {
            return \false;
        }
        if ($node->expr->isFirstClassCallable()) {
            return \false;
        }
        foreach ($node->expr->getArgs() as $arg) {
            if ($arg->value instanceof Variable && $this->isName($arg->value, $paramName)) {
                return \true;
            }
        }
        return \false;
    }
    private function isEmptyOrEchoedOrCasted(Node $node, string $paramName) : bool
    {
        if ($node instanceof Empty_ && $node->expr instanceof Variable && $this->isName($node->expr, $paramName)) {
            return \true;
        }
        if ($this->isEchoed($node, $paramName)) {
            return \true;
        }
        return $node instanceof Array_ && $node->expr instanceof Variable && $this->isName($node->expr, $paramName);
    }
    private function isMethodCallOrArrayDimFetch(string $paramName, ?Node $node) : bool
    {
        if ($node instanceof MethodCall) {
            return $node->var instanceof Variable && $this->isName($node->var, $paramName);
        }
        if ($node instanceof ArrayDimFetch) {
            return $node->var instanceof Variable && $this->isName($node->var, $paramName);
        }
        return \false;
    }
}
