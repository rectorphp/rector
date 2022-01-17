<?php

declare (strict_types=1);
namespace Rector\Doctrine\ValueObject;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
final class AssignToPropertyFetch
{
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\Assign
     */
    private $assign;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\PropertyFetch
     */
    private $propertyFetch;
    /**
     * @readonly
     * @var string
     */
    private $propertyName;
    public function __construct(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Expr\PropertyFetch $propertyFetch, string $propertyName)
    {
        $this->assign = $assign;
        $this->propertyFetch = $propertyFetch;
        $this->propertyName = $propertyName;
    }
    public function getAssign() : \PhpParser\Node\Expr\Assign
    {
        return $this->assign;
    }
    public function getPropertyFetch() : \PhpParser\Node\Expr\PropertyFetch
    {
        return $this->propertyFetch;
    }
    public function getPropertyName() : string
    {
        return $this->propertyName;
    }
}
