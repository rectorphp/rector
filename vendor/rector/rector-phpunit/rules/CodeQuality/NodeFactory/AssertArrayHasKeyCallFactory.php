<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use Rector\PHPUnit\Enum\PHPUnitClassName;
final class AssertArrayHasKeyCallFactory
{
    public function create(Variable $dimFetchVariable, Expr $dimExpr, Scope $scope): Expression
    {
        $args = $this->createArgs($dimFetchVariable, $dimExpr);
        if ($this->isInsideTestCase($scope)) {
            $call = new MethodCall(new Variable('this'), 'assertArrayHasKey', $args);
            return new Expression($call);
        }
        $call = new StaticCall(new FullyQualified(PHPUnitClassName::ASSERT), 'assertArrayHasKey', $args);
        return new Expression($call);
    }
    private function isInsideTestCase(Scope $scope): bool
    {
        if (!$scope->isInClass()) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        return $classReflection->is(PHPUnitClassName::TEST_CASE);
    }
    /**
     * @return Arg[]
     */
    private function createArgs(Variable $dimFetchVariable, Expr $dimExpr): array
    {
        // add detailed error: 'Existing keys are: ' . implode(', ', array_keys($data))
        $arrayKeysFuncCall = new FuncCall(new Name('array_keys'), [new Arg($dimFetchVariable)]);
        $implodeFuncCall = new FuncCall(new Name('implode'), [new Arg(new String_(', ')), new Arg($arrayKeysFuncCall)]);
        $concat = new Concat(new String_('Existing keys are: '), $implodeFuncCall);
        return [new Arg($dimExpr), new Arg($dimFetchVariable), new Arg($concat)];
    }
}
