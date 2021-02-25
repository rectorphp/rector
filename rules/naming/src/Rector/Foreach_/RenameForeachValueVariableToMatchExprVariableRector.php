<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Foreach_;

use Doctrine\Inflector\Inflector;
use PhpParser\Node;
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
        return new RuleDefinition(
            'Renames value variable name in foreach loop to match expression variable',
            [
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
     * @return string[]
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
        /** @var string $exprName */
        $exprName = $this->getName($node->expr);
        $keyVarName = $node->keyVar === null ? '' : (string) $this->getName($node->keyVar);
        /** @var string $valueVarName */
        $valueVarName = $this->getName($node->valueVar);
        $singularValueVarName = $this->inflector->singularize($exprName);

        if ($this->shouldSkip($keyVarName, $valueVarName, $singularValueVarName, $node->stmts)) {
            return null;
        }

        $node->valueVar = new Variable($singularValueVarName);
        $this->traverseNodesWithCallable($node->stmts, function (Node $node) use (
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

        return $node;
    }

    /**
     * @param Node[] $stmts
     */
    private function shouldSkip(
        string $keyVarName,
        string $valueVarName,
        string $singularValueVarName,
        array $stmts
    ): bool {
        if ($singularValueVarName === $valueVarName) {
            return true;
        }

        $isUsedInStmts = (bool) $this->betterNodeFinder->findFirst($stmts, function (Node $node) use (
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

        return $keyVarName === $singularValueVarName;
    }
}
