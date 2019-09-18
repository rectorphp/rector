<?php declare(strict_types=1);

namespace Rector\Php\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\StaticVar;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\NodeTraverser;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/vimeo/psalm/blob/29b70442b11e3e66113935a2ee22e165a70c74a4/docs/fixing_code.md#possiblyundefinedvariable
 * @see https://3v4l.org/MZFel
 * @see \Rector\Php\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\AddDefaultValueForUndefinedVariableRectorTest
 */
final class AddDefaultValueForUndefinedVariableRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $undefinedVariables = [];

    /**
     * @var string[]
     */
    private $definedVariables = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Adds default value for undefined variable', [
            new CodeSample(
                <<<'PHP'
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
PHP
                ,
                <<<'PHP'
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
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FunctionLike::class];
    }

    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->definedVariables = [];
        $this->undefinedVariables = [];

        $this->traverseNodesWithCallable((array) $node->stmts, function (Node $node): ?int {
            // entering new scope - break!
            if ($node instanceof FunctionLike) {
                return NodeTraverser::STOP_TRAVERSAL;
            }

            $this->collectDefinedVariablesFromForeach($node);

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

            $this->undefinedVariables[] = $variableName;

            return null;
        });

        if ($this->undefinedVariables === []) {
            return null;
        }

        $this->undefinedVariables = array_unique($this->undefinedVariables);

        $variablesInitiation = [];
        foreach ($this->undefinedVariables as $undefinedVariable) {
            if (in_array($undefinedVariable, $this->definedVariables, true)) {
                continue;
            }

            $variablesInitiation[] = new Expression(new Assign(new Variable($undefinedVariable), $this->createNull()));
        }

        $node->stmts = array_merge($variablesInitiation, (array) $node->stmts);

        return $node;
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

    private function shouldSkipVariable(Variable $variable): bool
    {
        $parentNode = $variable->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Node) {
            if ($parentNode instanceof Assign || $parentNode instanceof AssignRef || $this->isStaticVariable(
                $parentNode
            )) {
                return true;
            }
        }

        if ($parentNode instanceof Unset_ || $parentNode instanceof Node\Expr\Cast\Unset_) {
            return true;
        }

        // list() = | [$values] = defines variables as null
        if ($parentNode instanceof Node) {
            $parentParentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentParentNode instanceof List_ || $parentParentNode instanceof Array_) {
                return true;
            }
        }

        /** @var Scope|null $nodeScope */
        $nodeScope = $variable->getAttribute(AttributeKey::SCOPE);

        if ($nodeScope === null) {
            return true;
        }

        $variableName = $this->getName($variable);
        if ($variableName === null) {
            return true;
        }

        return false;
    }

    private function collectDefinedVariablesFromForeach(Node $node): void
    {
        if (! $node instanceof Foreach_) {
            return;
        }

        $this->traverseNodesWithCallable($node->stmts, function (Node $node): void {
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
}
