<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver\NestedMethodCallSource\Form\Controls;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver\NestedMethodCallSource\Form\Rules;

class TextArea
{
    public function addCondition($rule): Rules
    {
    }
}
