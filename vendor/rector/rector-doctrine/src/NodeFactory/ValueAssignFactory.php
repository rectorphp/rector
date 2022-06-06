<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Doctrine\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\New_;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
final class ValueAssignFactory
{
    public function createDefaultDateTimeAssign(string $propertyName) : Expression
    {
        $propertyFetch = $this->createPropertyFetch($propertyName);
        $assign = new Assign($propertyFetch, $this->createNewDateTime());
        return new Expression($assign);
    }
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
