<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\FormHelper\FormTypeStringToTypeProvider;

abstract class AbstractFormAddRector extends AbstractRector
{
    /**
     * @var FormTypeStringToTypeProvider
     */
    protected $formTypeStringToTypeProvider;

    public function __construct(FormTypeStringToTypeProvider $formTypeStringToTypeProvider)
    {
        $this->formTypeStringToTypeProvider = $formTypeStringToTypeProvider;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    protected function isFormAddMethodCall(MethodCall $methodCall): bool
    {
        if (! $this->isObjectType($methodCall->var, 'Symfony\Component\Form\FormBuilderInterface')) {
            return false;
        }

        if (! $this->isName($methodCall->name, 'add')) {
            return false;
        }

        // just one argument
        if (! isset($methodCall->args[1])) {
            return false;
        }

        return $methodCall->args[1]->value !== null;
    }

    protected function matchOptionsArray(MethodCall $methodCall): ?Array_
    {
        if (! isset($methodCall->args[2])) {
            return null;
        }

        $optionsArray = $methodCall->args[2]->value;
        if (! $optionsArray instanceof Array_) {
            return null;
        }

        return $optionsArray;
    }

    protected function isCollectionType(MethodCall $methodCall): bool
    {
        $typeValue = $methodCall->args[1]->value;

        if ($typeValue instanceof ClassConstFetch
            && $this->isName($typeValue->class, 'Symfony\Component\Form\Extension\Core\Type\CollectionType')
        ) {
            return true;
        }

        return $this->isValue($typeValue, 'collection');
    }
}
