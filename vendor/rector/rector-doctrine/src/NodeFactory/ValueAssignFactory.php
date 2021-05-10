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
    public function createDefaultDateTimeAssign(string $propertyName) : \PhpParser\Node\Stmt\Expression
    {
        $propertyFetch = $this->createPropertyFetch($propertyName);
        $assign = new \PhpParser\Node\Expr\Assign($propertyFetch, $this->createNewDateTime());
        return new \PhpParser\Node\Stmt\Expression($assign);
    }
    public function createDefaultDateTimeWithValueAssign(string $propertyName, \PhpParser\Node\Expr $defaultExpr) : \PhpParser\Node\Stmt\Expression
    {
        $propertyFetch = $this->createPropertyFetch($propertyName);
        $newDateTime = $this->createNewDateTime();
        $this->addDateTimeArgumentIfNotDefault($defaultExpr, $newDateTime);
        $assign = new \PhpParser\Node\Expr\Assign($propertyFetch, $newDateTime);
        return new \PhpParser\Node\Stmt\Expression($assign);
    }
    private function createPropertyFetch(string $propertyName) : \PhpParser\Node\Expr\PropertyFetch
    {
        return new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), $propertyName);
    }
    private function createNewDateTime() : \PhpParser\Node\Expr\New_
    {
        return new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name\FullyQualified('DateTime'));
    }
    private function addDateTimeArgumentIfNotDefault(\PhpParser\Node\Expr $defaultExpr, \PhpParser\Node\Expr\New_ $dateTimeNew) : void
    {
        if ($defaultExpr instanceof \PhpParser\Node\Scalar\String_ && ($defaultExpr->value === 'now' || $defaultExpr->value === 'now()')) {
            return;
        }
        $dateTimeNew->args[] = new \PhpParser\Node\Arg($defaultExpr);
    }
}
