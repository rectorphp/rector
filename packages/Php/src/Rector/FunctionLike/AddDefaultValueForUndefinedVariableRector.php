<?php declare(strict_types=1);

namespace Rector\Php\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\StaticVar;
use PhpParser\Node\Stmt\Unset_;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php\Exception\BreakScopeException;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/vimeo/psalm/blob/29b70442b11e3e66113935a2ee22e165a70c74a4/docs/fixing_code.md#possiblyundefinedvariable
 * @see https://3v4l.org/MZFel
 */
final class AddDefaultValueForUndefinedVariableRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $undefinedVariables = [];

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    public function __construct(CallableNodeTraverser $callableNodeTraverser)
    {
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

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
        return [FunctionLike::class];
    }

    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->undefinedVariables = [];

        try {
            $this->callableNodeTraverser->traverseNodesWithCallable((array) $node->stmts, function (Node $node) {
                // entering new scope - break!
                if ($node instanceof FunctionLike) {
                    throw new BreakScopeException();
                }

                if (! $node instanceof Variable) {
                    return null;
                }

                if ($this->shouldSkipVariable($node)) {
                    return null;
                }

                $variableName = $this->getName($node);
                if ($variableName === null) {
                    return null;
                }

                // defined 100 %
                /** @var Scope $nodeScope */
                $nodeScope = $node->getAttribute(AttributeKey::SCOPE);
                if ($nodeScope->hasVariableType($variableName)->yes()) {
                    return null;
                }

                $this->undefinedVariables[] = $variableName;
            });
        } catch (BreakScopeException $breakScopeException) {
            // @ignoreException
        }

        if ($this->undefinedVariables === []) {
            return null;
        }

        $this->undefinedVariables = array_unique($this->undefinedVariables);

        $variablesInitiation = [];
        foreach ($this->undefinedVariables as $undefinedVariable) {
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
            if ($parentNode instanceof Assign || $this->isStaticVariable($parentNode)) {
                return true;
            }
        }

        if ($parentNode instanceof Unset_ || $parentNode instanceof Node\Expr\Cast\Unset_) {
            return true;
        }

        /** @var Scope|null $nodeScope */
        $nodeScope = $variable->getAttribute(AttributeKey::SCOPE);

        return $nodeScope === null;
    }
}
