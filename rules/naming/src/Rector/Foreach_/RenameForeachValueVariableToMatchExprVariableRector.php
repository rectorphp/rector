<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Foreach_;

use Doctrine\Inflector\Inflector;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Foreach_;
use Rector\CodeQuality\NodeAnalyzer\ForeachAnalyzer;
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

    /**
     * @var ForeachAnalyzer
     */
    private $foreachAnalyzer;

    public function __construct(Inflector $inflector, ForeachAnalyzer $foreachAnalyzer)
    {
        $this->inflector       = $inflector;
        $this->foreachAnalyzer = $foreachAnalyzer;
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
        $exprName             = $this->getName($node->expr);
        $singularValueVarName = $this->inflector->singularize($exprName);
        $valueVarName         = $this->getName($node->valueVar);

        if ($singularValueVarName === $valueVarName) {
            return null;
        }

        $node->valueVar = new Variable($singularValueVarName);
        $this->traverseNodesWithCallable($node->stmts, function (Node $node) use ($singularValueVarName, $valueVarName): ?Variable {
            if (! $node instanceof Variable) {
                return null;
            }

            $nodeName = $this->getName($node);
            if ($nodeName !== $valueVarName) {
                return null;
            }

            $node = new Variable($singularValueVarName);
            return $node;
        });

        return $node;
    }
}
