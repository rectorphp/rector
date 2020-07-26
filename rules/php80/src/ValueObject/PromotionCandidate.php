<?php

declare(strict_types=1);

namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;

final class PromotionCandidate
{
    /**
     * @var Property
     */
    private $property;

    /**
     * @var Assign
     */
    private $assign;

    /**
     * @var Param
     */
    private $param;

    public function __construct(Property $property, Assign $assign, Param $param)
    {
        $this->property = $property;
        $this->assign = $assign;
        $this->param = $param;
    }

    public function getProperty(): Property
    {
        return $this->property;
    }

    public function getAssign(): Assign
    {
        return $this->assign;
    }

    public function getParam(): Param
    {
        return $this->param;
    }
}
