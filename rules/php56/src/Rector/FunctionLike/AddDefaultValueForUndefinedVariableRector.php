<?php

declare(strict_types=1);

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
use Rector\Core\Util\StaticInstanceOf;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/vimeo/psalm/blob/29b70442b11e3e66113935a2ee22e165a70c74a4/docs/fixing_code.md#possiblyundefinedvariable
 * @see https://3v4l.org/MZFel
 *
 * @see \Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\AddDefaultValueForUndefinedVariableRectorTest
 */
final class AddDefaultValueForUndefinedVariableRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $definedVariables = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Adds default value for undefined variable', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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
                ,
                <<<'CODE_SAMPLE'
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
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class, Closure::class];
    }

    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->definedVariables = [];

        $undefinedVariables = $this->collectUndefinedVariableScope($node);
        if ($undefinedVariables === []) {
            return null;
        }

        $variablesInitiation = [];
        foreach ($undefinedVariables as $undefinedVariable) {
            if (in_array($undefinedVariable, $this->definedVariables, true)) {
                continue;
            }

            $value = $this->isArray($undefinedVariable, (array) $node->stmts)
                ? new Array_([])
                : $this->nodeFactory->createNull();

            $variablesInitiation[] = new Expression(new Assign(new Variable($undefinedVariable), $value));
        }

        $node->stmts = array_merge($variablesInitiation, (array) $node->stmts);

        return $node;
    }

    /**
     * @param ClassMethod|Function_|Closure $node
     * @return string[]
     */
    private function collectUndefinedVariableScope(Node $node): array
    {
        $undefinedVariables = [];

        $this->traverseNodesWithCallable((array) $node->stmts, function (Node $node) use (&$undefinedVariables): ?int {
            // entering new scope - break!
            if ($node instanceof FunctionLike && ! $node instanceof ArrowFunction) {
                return NodeTraverser::STOP_TRAVERSAL;
            }

            if ($node instanceof Foreach_) {
                // handled above
                $this->collectDefinedVariablesFromForeach($node);
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }

            if (! $node instanceof Variable) {
                return null;
            }

            if ($this->shouldSkipVariable($node)) {
                return null;
            }

            /** @var string $variableName */
            $variableName = $this->getName($node);

            // defined 100 %
            /** @var Scope $nodeScope */
            $nodeScope = $node->getAttribute(AttributeKey::SCOPE);
            if ($nodeScope->hasVariableType($variableName)->yes()) {
                return null;
            }

            $undefinedVariables[] = $variableName;

            return null;
        });

        return array_unique($undefinedVariables);
    }

    /**
     * @param Stmt[] $stmts
     */
    private function isArray(string $undefinedVariable, array $stmts): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($stmts, function (Node $node) use (
            $undefinedVariable
        ): bool {
            if (! $node instanceof ArrayDimFetch) {
                return false;
            }

            return $this->isName($node->var, $undefinedVariable);
        });
    }

    private function collectDefinedVariablesFromForeach(Foreach_ $foreach): void
    {
        $this->traverseNodesWithCallable($foreach->stmts, function (Node $node): void {
            if ($node instanceof Assign || $node instanceof AssignRef) {
                if (! $node->var instanceof Variable) {
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

    private function shouldSkipVariable(Variable $variable): bool
    {
        $parentNode = $variable->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Node) {
            return true;
        }

        if ($parentNode instanceof Global_) {
            return true;
        }

        if ($parentNode instanceof Node &&
            ($parentNode instanceof Assign || $parentNode instanceof AssignRef || $this->isStaticVariable($parentNode)
        )) {
            return true;
        }
        if (StaticInstanceOf::isOneOf($parentNode, [Unset_::class, UnsetCast::class])) {
            return true;
        }

        // list() = | [$values] = defines variables as null
        if ($this->isListAssign($parentNode)) {
            return true;
        }

        $nodeScope = $variable->getAttribute(AttributeKey::SCOPE);
        if (! $nodeScope instanceof Scope) {
            return true;
        }

        $variableName = $this->getName($variable);

        // skip $this, as probably in outer scope
        if ($variableName === 'this') {
            return true;
        }

        return $variableName === null;
    }

    private function isStaticVariable(Node $parentNode): bool
    {
        // definition of static variable
        if ($parentNode instanceof StaticVar) {
            $parentParentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentParentNode instanceof Static_) {
                return true;
            }
        }

        return false;
    }

    private function isListAssign(Node $node): bool
    {
        $parentParentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        return StaticInstanceOf::isOneOf($parentParentNode, [List_::class, Array_::class]);
    }
}
