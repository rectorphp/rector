<?php

declare (strict_types=1);
namespace Rector\Php56\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\Cast\Unset_ as UnsetCast;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Global_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\StaticVar;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\NodeTraverser;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/vimeo/psalm/blob/29b70442b11e3e66113935a2ee22e165a70c74a4/docs/fixing_code.md#possiblyundefinedvariable
 * @see https://3v4l.org/MZFel
 *
 * @see \Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\AddDefaultValueForUndefinedVariableRectorTest
 */
final class AddDefaultValueForUndefinedVariableRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string[]
     */
    private $definedVariables = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Adds default value for undefined variable', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if (rand(0, 1)) {
            $a = 5;
        }
        echo $a;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $a = null;
        if (rand(0, 1)) {
            $a = 5;
        }
        echo $a;
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
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Expr\Closure::class];
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $this->definedVariables = [];
        $undefinedVariables = $this->collectUndefinedVariableScope($node);
        if ($undefinedVariables === []) {
            return null;
        }
        $variablesInitiation = [];
        foreach ($undefinedVariables as $undefinedVariable) {
            if (\in_array($undefinedVariable, $this->definedVariables, \true)) {
                continue;
            }
            $value = $this->isArray($undefinedVariable, (array) $node->stmts) ? new \PhpParser\Node\Expr\Array_([]) : $this->nodeFactory->createNull();
            $variablesInitiation[] = new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable($undefinedVariable), $value));
        }
        $node->stmts = \array_merge($variablesInitiation, (array) $node->stmts);
        return $node;
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     * @return string[]
     */
    private function collectUndefinedVariableScope(\PhpParser\Node $node) : array
    {
        $undefinedVariables = [];
        $this->traverseNodesWithCallable((array) $node->stmts, function (\PhpParser\Node $node) use(&$undefinedVariables) : ?int {
            // entering new scope - break!
            if ($node instanceof \PhpParser\Node\FunctionLike && !$node instanceof \PhpParser\Node\Expr\ArrowFunction) {
                return \PhpParser\NodeTraverser::STOP_TRAVERSAL;
            }
            if ($node instanceof \PhpParser\Node\Stmt\Foreach_) {
                // handled above
                $this->collectDefinedVariablesFromForeach($node);
                return \PhpParser\NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return null;
            }
            if ($this->shouldSkipVariable($node)) {
                return null;
            }
            /** @var string $variableName */
            $variableName = $this->getName($node);
            // defined 100 %
            /** @var Scope $scope */
            $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
            if ($scope->hasVariableType($variableName)->yes()) {
                return null;
            }
            $undefinedVariables[] = $variableName;
            return null;
        });
        return \array_unique($undefinedVariables);
    }
    /**
     * @param Stmt[] $stmts
     */
    private function isArray(string $undefinedVariable, array $stmts) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($stmts, function (\PhpParser\Node $node) use($undefinedVariable) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
                return \false;
            }
            return $this->isName($node->var, $undefinedVariable);
        });
    }
    private function collectDefinedVariablesFromForeach(\PhpParser\Node\Stmt\Foreach_ $foreach) : void
    {
        $this->traverseNodesWithCallable($foreach->stmts, function (\PhpParser\Node $node) : void {
            if ($node instanceof \PhpParser\Node\Expr\Assign || $node instanceof \PhpParser\Node\Expr\AssignRef) {
                if (!$node->var instanceof \PhpParser\Node\Expr\Variable) {
                    return;
                }
                $variableName = $this->getName($node->var);
                if ($variableName === null) {
                    return;
                }
                $this->definedVariables[] = $variableName;
            }
        });
    }
    private function shouldSkipVariable(\PhpParser\Node\Expr\Variable $variable) : bool
    {
        $parentNode = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node) {
            return \true;
        }
        if ($parentNode instanceof \PhpParser\Node\Stmt\Global_) {
            return \true;
        }
        if ($parentNode instanceof \PhpParser\Node && ($parentNode instanceof \PhpParser\Node\Expr\Assign || $parentNode instanceof \PhpParser\Node\Expr\AssignRef || $this->isStaticVariable($parentNode))) {
            return \true;
        }
        if ($parentNode instanceof \PhpParser\Node\Stmt\Unset_ || $parentNode instanceof \PhpParser\Node\Expr\Cast\Unset_) {
            return \true;
        }
        // list() = | [$values] = defines variables as null
        if ($this->isListAssign($parentNode)) {
            return \true;
        }
        $nodeScope = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$nodeScope instanceof \PHPStan\Analyser\Scope) {
            return \true;
        }
        $variableName = $this->getName($variable);
        // skip $this, as probably in outer scope
        if ($variableName === 'this') {
            return \true;
        }
        return $variableName === null;
    }
    private function isStaticVariable(\PhpParser\Node $parentNode) : bool
    {
        // definition of static variable
        if ($parentNode instanceof \PhpParser\Node\Stmt\StaticVar) {
            $parentParentNode = $parentNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if ($parentParentNode instanceof \PhpParser\Node\Stmt\Static_) {
                return \true;
            }
        }
        return \false;
    }
    private function isListAssign(\PhpParser\Node $node) : bool
    {
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentNode instanceof \PhpParser\Node\Expr\List_) {
            return \true;
        }
        return $parentNode instanceof \PhpParser\Node\Expr\Array_;
    }
}
