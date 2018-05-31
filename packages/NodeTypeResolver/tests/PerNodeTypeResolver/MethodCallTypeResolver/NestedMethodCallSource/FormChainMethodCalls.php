<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver\NestedMethodCallSource;

use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver\NestedMethodCallSource\Application\UI\Form;

final class FormChainMethodCalls
{
    public function createNetteForm()
    {
        $form = new Form();
        $form->addText('name')
            ->addCondition($form::FILLED)
            ->addRule('...');

        return $form;
    }
}
