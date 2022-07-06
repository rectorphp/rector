<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
final class ValueAssignFactory
{
    public function createDefaultDateTimeWithValueAssign(string $propertyName, Expr $defaultExpr) : Expression
    {
        $propertyFetch = $this->createPropertyFetch($propertyName);
        $newDateTime = $this->createNewDateTime();
        $this->addDateTimeArgumentIfNotDefault($defaultExpr, $newDateTime);
        $assign = new Assign($propertyFetch, $newDateTime);
        return new Expression($assign);
    }
    private function createPropertyFetch(string $propertyName) : PropertyFetch
    {
        return new PropertyFetch(new Variable('this'), $propertyName);
    }
    private function createNewDateTime() : New_
    {
        return new New_(new FullyQualified('DateTime'));
    }
    private function addDateTimeArgumentIfNotDefault(Expr $defaultExpr, New_ $dateTimeNew) : void
    {
        if ($defaultExpr instanceof String_ && ($defaultExpr->value === 'now' || $defaultExpr->value === 'now()')) {
            return;
        }
        $dateTimeNew->args[] = new Arg($defaultExpr);
    }
}
