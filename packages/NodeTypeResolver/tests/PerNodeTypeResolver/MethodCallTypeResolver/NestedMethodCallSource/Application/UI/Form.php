<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver\NestedMethodCallSource\Application\UI;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver\NestedMethodCallSource\Form\Controls\TextInput;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver\NestedMethodCallSource\Form\Form as BaseForm;

final class Form extends BaseForm
{
    const FILLED = 'filled';

    public function addText($name): TextInput
    {
    }
}
