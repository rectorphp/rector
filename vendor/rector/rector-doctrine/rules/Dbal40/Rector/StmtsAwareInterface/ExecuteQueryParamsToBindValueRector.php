<?php

declare (strict_types=1);
namespace Rector\Doctrine\Dbal40\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\NodeFinder;
use PHPStan\Type\ObjectType;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
final class ExecuteQueryParamsToBindValueRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change executeQuery() with parameters to bindValue() with explicit values', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\DBAL\Statement;

class SomeClass
{
    public function run(Statement $statement, array $params): void
    {
        $result = $statement->executeQuery($params)
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\DBAL\Statement;

class SomeClass
{
    public function run(Statement $statement, array $params): void
    {
        foreach ($params as $key=> $value) {
            $statement->bindValue($key + 1, $value);
        }

        $result = $statement->executeQuery();
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
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?StmtsAwareInterface
    {
        $nodeFinder = new NodeFinder();
        $hasChanged = \false;
        $objectType = new ObjectType('Doctrine\\DBAL\\Statement');
        foreach ((array) $node->stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            $executeQueryMethodCall = $nodeFinder->findFirst($stmt, function (Node $node) use($objectType) : bool {
                if (!$node instanceof MethodCall) {
                    return \false;
                }
                if (!$this->isObjectType($node->var, $objectType)) {
                    return \false;
                }
                if (!$this->isName($node->name, 'executeQuery')) {
                    return \false;
                }
                return \count($node->getArgs()) === 1;
            });
            if (!$executeQueryMethodCall instanceof MethodCall) {
                continue;
            }
            // remove args
            $stmtsExpr = $executeQueryMethodCall->getArgs()[0]->value;
            $executeQueryMethodCall->args = [];
            $hasChanged = \true;
            $bindValueForeach = $this->createBindValueForeach($executeQueryMethodCall->var, $stmtsExpr);
            \array_splice($node->stmts, $key, 1, [$bindValueForeach, $stmt]);
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function createBindValueForeach(Expr $statementExpr, Expr $stmtsExpr) : Foreach_
    {
        $positionVariable = new Variable('position');
        $parameterVariable = new Variable('parameter');
        $foreach = new Foreach_($stmtsExpr, $parameterVariable, ['keyVar' => $positionVariable]);
        $bindValueMethodCall = new MethodCall($statementExpr, 'bindValue', [new Arg(new Plus($positionVariable, new LNumber(1))), new Arg($parameterVariable)]);
        $foreach->stmts[] = new Expression($bindValueMethodCall);
        return $foreach;
    }
}
