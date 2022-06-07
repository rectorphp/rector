<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
final class PropertyPromotionCandidate
{
    /**
     * @readonly
     * @var \PhpParser\Node\Stmt\Property
     */
    private $property;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\Assign
     */
    private $assign;
    /**
     * @readonly
     * @var \PhpParser\Node\Param
     */
    private $param;
    public function __construct(Property $property, Assign $assign, Param $param)
    {
        $this->property = $property;
        $this->assign = $assign;
        $this->param = $param;
    }
    public function getProperty() : Property
    {
        return $this->property;
    }
    public function getAssign() : Assign
    {
        return $this->assign;
    }
    public function getParam() : Param
    {
        return $this->param;
    }
}
