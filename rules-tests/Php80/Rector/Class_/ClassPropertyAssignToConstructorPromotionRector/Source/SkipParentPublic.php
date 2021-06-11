<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Source;

abstract class SkipParentPublic
{
    public $fixer = null;

    public function __construct()
    {
        $this->fixer = new Fixer();
    }
}
