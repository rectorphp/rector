<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
final class PropertyPromotionCandidate
{
    /**
     * @var \PhpParser\Node\Stmt\Property
     */
    private $property;
    /**
     * @var \PhpParser\Node\Expr\Assign
     */
    private $assign;
    /**
     * @var \PhpParser\Node\Param
     */
    private $param;
    public function __construct(\PhpParser\Node\Stmt\Property $property, \PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Param $param)
    {
        $this->property = $property;
        $this->assign = $assign;
        $this->param = $param;
    }
    public function getProperty() : \PhpParser\Node\Stmt\Property
    {
        return $this->property;
    }
    public function getAssign() : \PhpParser\Node\Expr\Assign
    {
        return $this->assign;
    }
    public function getParam() : \PhpParser\Node\Param
    {
        return $this->param;
    }
}
