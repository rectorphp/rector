<?php

declare(strict_types=1);

namespace Rector\Php56\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\Cast\Unset_ as UnsetCast;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
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
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Adds default value for undefined variable', [
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

            $variablesInitiation[] = new Expression(new Assign(new Variable($undefinedVariable), $this->createNull()));
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

    private function collectDefinedVariablesFromForeach(Foreach_ $foreach): void
    {
        $this->traverseNodesWithCallable((array) $foreach->stmts, function (Node $node): void {
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
        if ($parentNode instanceof Global_) {
            return true;
        }

        if ($parentNode instanceof Node &&
            ($parentNode instanceof Assign || $parentNode instanceof AssignRef || $this->isStaticVariable($parentNode)
        )) {
            return true;
        }

        if ($parentNode instanceof Unset_ || $parentNode instanceof UnsetCast) {
            return true;
        }

        // list() = | [$values] = defines variables as null
        if ($this->isListAssign($parentNode)) {
            return true;
        }

        /** @var Scope|null $nodeScope */
        $nodeScope = $variable->getAttribute(AttributeKey::SCOPE);
        if ($nodeScope === null) {
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

    private function isListAssign(?Node $parentNode): bool
    {
        if ($parentNode instanceof Node) {
            $parentParentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentParentNode instanceof List_ || $parentParentNode instanceof Array_) {
                return true;
            }
        }

        return false;
    }
}
