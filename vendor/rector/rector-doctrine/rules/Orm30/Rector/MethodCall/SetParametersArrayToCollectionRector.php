<?php

declare (strict_types=1);
namespace Rector\Doctrine\Orm30\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/doctrine/orm/pull/9490
 * @see https://github.com/doctrine/orm/blob/3.0.x/UPGRADE.md#query-querybuilder-and-nativequery-parameters-bc-break
 */
final class SetParametersArrayToCollectionRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change the argument type for setParameters from array to ArrayCollection and Parameter calls', [new CodeSample(<<<'CODE_SAMPLE'
$entityManager->createQueryBuilder()->setParameters([
    'foo' => 'bar'
]);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$entityManager->createQueryBuilder()->setParameters(new \Doctrine\Common\Collections\ArrayCollection([
    new \Doctrine\ORM\Query\Parameter('foo', 'bar')
]));
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, MethodCall::class];
    }
    /**
     * @param ClassMethod|MethodCall $node
     */
    public function refactor(Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof ClassMethod) {
            $variables = $this->getAffectedVariables($node);
            $hasChanges = \false;
            foreach ($variables as $variable) {
                $nodeStatements = $node->getStmts();
                if ($nodeStatements !== null && $this->changeArrayToCollection($nodeStatements, $variable)) {
                    $hasChanges = \true;
                }
            }
            if ($hasChanges) {
                return $node;
            }
            return null;
        }
        return $this->refactorMethodCall($node);
    }
    /**
     * @param array<Node\Stmt> $stmts
     */
    public function changeArrayToCollection(array $stmts, Variable $variable) : bool
    {
        $hasChanges = \false;
        foreach ($stmts as $stmt) {
            if ($stmt instanceof StmtsAwareInterface) {
                if ($this->changeArrayToCollection($stmt->stmts ?? [], $variable)) {
                    $hasChanges = \true;
                }
                continue;
            }
            if (!$stmt instanceof Expression) {
                continue;
            }
            if ($stmt->expr instanceof Assign && $stmt->expr->expr instanceof Array_ && $stmt->expr->var instanceof Variable && $stmt->expr->var->name === $variable->name) {
                $newCollection = new New_(new FullyQualified('Doctrine\\Common\\Collections\\ArrayCollection'));
                $newCollection->args = [new Arg(new Array_($this->convertArrayToParameters($stmt->expr->expr)['parameters']))];
                $stmt->expr->expr = $newCollection;
                $hasChanges = \true;
            }
            if ($stmt->expr instanceof Assign && $stmt->expr->var instanceof ArrayDimFetch && $stmt->expr->var->dim instanceof Expr && $stmt->expr->var->var instanceof Variable && $stmt->expr->var->var->name === $variable->name) {
                $newParameter = new New_(new FullyQualified('Doctrine\\ORM\\Query\\Parameter'));
                $newParameter->args = [new Arg($stmt->expr->var->dim), new Arg($stmt->expr->expr)];
                $stmt->expr = new MethodCall($stmt->expr->var->var, 'add', [new Arg($newParameter)]);
                $hasChanges = \true;
            }
        }
        return $hasChanges;
    }
    /**
     * @return iterable<Variable>
     */
    private function getAffectedVariables(ClassMethod $classMethod) : iterable
    {
        $statements = $classMethod->getStmts() ?? [];
        foreach ($statements as $statement) {
            if ($statement instanceof Expression && $statement->expr instanceof MethodCall && $statement->expr->args[0] instanceof Arg && $statement->expr->args[0]->value instanceof Variable && $statement->expr->name instanceof Identifier && $statement->expr->name->name === 'setParameters') {
                $varType = $this->nodeTypeResolver->getType($statement->expr->var);
                if (!$varType instanceof ObjectType || !$varType->isInstanceOf('Doctrine\\ORM\\QueryBuilder')->yes()) {
                    continue;
                }
                (yield $statement->expr->args[0]->value);
            }
        }
    }
    private function refactorMethodCall(MethodCall $methodCall) : ?\PhpParser\Node
    {
        $varType = $this->nodeTypeResolver->getType($methodCall->var);
        if (!$varType instanceof ObjectType) {
            return null;
        }
        if (!$varType->isInstanceOf('Doctrine\\ORM\\QueryBuilder')->yes()) {
            return null;
        }
        if ($methodCall->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isNames($methodCall->name, ['setParameters'])) {
            return null;
        }
        $args = $methodCall->getArgs();
        if (\count($args) !== 1) {
            return null;
        }
        $currentArg = $args[0]->value;
        $isAlreadyAnArrayCollection = \false;
        $currentArgType = $this->nodeTypeResolver->getType($currentArg);
        if ($currentArgType instanceof ObjectType && $currentArgType->isInstanceOf('Doctrine\\Common\\Collections\\ArrayCollection')->yes() && $currentArg instanceof New_ && \count($currentArg->args) === 1 && $currentArg->args[0] instanceof Arg) {
            $currentArg = $currentArg->args[0]->value;
            $isAlreadyAnArrayCollection = \true;
        }
        if (!$currentArg instanceof Array_) {
            return null;
        }
        ['parameters' => $parameters, 'changedParameterType' => $changedParameterType] = $this->convertArrayToParameters($currentArg);
        if ($changedParameterType === \false && $isAlreadyAnArrayCollection) {
            return null;
        }
        $new = new New_(new FullyQualified('Doctrine\\Common\\Collections\\ArrayCollection'));
        $new->args = [new Arg(new Array_($parameters))];
        $methodCall->args = [new Arg($new)];
        return $methodCall;
    }
    /**
     * @return array{parameters: list<ArrayItem>, changedParameterType: bool}
     */
    private function convertArrayToParameters(Array_ $array) : array
    {
        $changedParameterType = \false;
        $parameters = [];
        foreach ($array->items as $index => $value) {
            if (!$value instanceof ArrayItem) {
                continue;
            }
            $arrayValueType = $this->nodeTypeResolver->getType($value->value);
            if (!$arrayValueType instanceof ObjectType || !$arrayValueType->isInstanceOf('Doctrine\\ORM\\Query\\Parameter')->yes()) {
                $newParameter = new New_(new FullyQualified('Doctrine\\ORM\\Query\\Parameter'));
                $newParameter->args = [new Arg($value->key ?? new LNumber($index)), new Arg($value->value)];
                $value->value = $newParameter;
                $changedParameterType = \true;
            }
            $parameters[] = new ArrayItem($value->value);
        }
        return ['parameters' => $parameters, 'changedParameterType' => $changedParameterType];
    }
}
