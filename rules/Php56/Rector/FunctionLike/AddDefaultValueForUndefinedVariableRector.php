<?php

declare(strict_types=1);

namespace Rector\Php56\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Rector\AbstractRector;
use Rector\Php56\NodeAnalyzer\UndefinedVariableResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://github.com/vimeo/psalm/blob/29b70442b11e3e66113935a2ee22e165a70c74a4/docs/fixing_code.md#possiblyundefinedvariable
 * @see https://3v4l.org/MZFel
 *
 * @see \Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\AddDefaultValueForUndefinedVariableRectorTest
 */
final class AddDefaultValueForUndefinedVariableRector extends AbstractRector
{
    /**
     * @var string
     */
    private const ALREADY_ADDED_VARIABLE_NAMES = 'already_added_variable_names';

    public function __construct(
        private UndefinedVariableResolver $undefinedVariableResolver
    ) {
    }

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
     * @return array<class-string<Node>>
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
        $undefinedVariableNames = $this->undefinedVariableResolver->resolve($node);

        // avoids adding same variable multiple tiemes
        $alreadyAddedVariableNames = (array) $node->getAttribute(self::ALREADY_ADDED_VARIABLE_NAMES);
        $undefinedVariableNames = array_diff($undefinedVariableNames, $alreadyAddedVariableNames);

        if ($undefinedVariableNames === []) {
            return null;
        }

        $variablesInitiation = [];
        foreach ($undefinedVariableNames as $undefinedVariableName) {
            $value = $this->isArray($undefinedVariableName, (array) $node->stmts)
                ? new Array_([])
                : $this->nodeFactory->createNull();

            $assign = new Assign(new Variable($undefinedVariableName), $value);
            $variablesInitiation[] = new Expression($assign);
        }

        $node->setAttribute(self::ALREADY_ADDED_VARIABLE_NAMES, $undefinedVariableNames);

        $node->stmts = array_merge($variablesInitiation, (array) $node->stmts);

        return $node;
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
}
