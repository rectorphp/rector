<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Foreach_;

use Doctrine\Inflector\Inflector;
use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\RenameForeachValueVariableToMatchExprVariableRectorTest
 */
final class RenameForeachValueVariableToMatchExprVariableRector extends AbstractRector
{
    /**
     * @var Inflector
     */
    private $inflector;

    public function __construct(Inflector $inflector)
    {
        $this->inflector = $inflector;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Renames value variable name in foreach loop to match expression variable', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
public function run()
{
    $array = [];
    foreach ($variables as $foo) {
        $array[] = $property;
    }
}
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
public function run()
{
    $array = [];
    foreach ($variables as $variable) {
        $array[] = $variable;
    }
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
        return [Foreach_::class];
    }

    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof Variable && ! $node->expr instanceof PropertyFetch) {
            return null;
        }

        $exprName = $this->getName($node->expr);
        if ($exprName === null) {
            return null;
        }

        if ($node->keyVar instanceof Node) {
            return null;
        }

        $valueVarName = $this->getName($node->valueVar);
        if ($valueVarName === null) {
            return null;
        }

        if (strpos($valueVarName, 'single') === 0) {
            return null;
        }

        $singularValueVarName = $this->inflector->singularize($exprName);
        $singularValueVarName = $singularValueVarName === $exprName
            ? 'single' . ucfirst($singularValueVarName)
            : $singularValueVarName;

        $length = strlen($singularValueVarName);
        if ($length >= 40) {
            return null;
        }

        if ($this->shouldSkip($valueVarName, $singularValueVarName, $node)) {
            return null;
        }

        return $this->processRename($node, $valueVarName, $singularValueVarName);
    }

    private function processRename(Foreach_ $foreach, string $valueVarName, string $singularValueVarName): Foreach_
    {
        $foreach->valueVar = new Variable($singularValueVarName);
        $this->traverseNodesWithCallable($foreach->stmts, function (Node $node) use (
            $singularValueVarName,
            $valueVarName
        ): ?Variable {
            if (! $node instanceof Variable) {
                return null;
            }

            if (! $this->isName($node, $valueVarName)) {
                return null;
            }
            return new Variable($singularValueVarName);
        });

        return $foreach;
    }

    private function shouldSkip(
        string $valueVarName,
        string $singularValueVarName,
        Foreach_ $foreach
    ): bool {
        if ($singularValueVarName === $valueVarName) {
            return true;
        }

        $isUsedInStmts = (bool) $this->betterNodeFinder->findFirst($foreach->stmts, function (Node $node) use (
            $singularValueVarName
        ): bool {
            if (! $node instanceof Variable) {
                return false;
            }

            return $this->isName($node, $singularValueVarName);
        });

        if ($isUsedInStmts) {
            return true;
        }

        return (bool) $this->betterNodeFinder->findFirstNext($foreach, function (Node $node) use (
            $singularValueVarName
        ): bool {
            if (! $node instanceof Variable) {
                return false;
            }

            return $this->isName($node, $singularValueVarName);
        });
    }
}
