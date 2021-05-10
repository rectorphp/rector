<?php

declare(strict_types=1);

namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;

final class PropertyPromotionCandidate
{
    public function __construct(
        private Property $property,
        private Assign $assign,
        private Param $param
    ) {
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
