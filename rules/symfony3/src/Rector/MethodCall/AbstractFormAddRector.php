<?php

declare(strict_types=1);

namespace Rector\Symfony3\Rector\MethodCall;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony3\FormHelper\FormTypeStringToTypeProvider;

abstract class AbstractFormAddRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const FORM_TYPES = ['Symfony\Component\Form\FormBuilderInterface', 'Symfony\Component\Form\FormInterface'];

    /**
     * @var FormTypeStringToTypeProvider
     */
    protected $formTypeStringToTypeProvider;

    /**
     * @required
     */
    public function autowireAbstractFormAddRector(FormTypeStringToTypeProvider $formTypeStringToTypeProvider): void
    {
        $this->formTypeStringToTypeProvider = $formTypeStringToTypeProvider;
    }

    protected function isFormAddMethodCall(MethodCall $methodCall): bool
    {
        if (! $this->isObjectTypes($methodCall->var, self::FORM_TYPES)) {
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

        return $this->valueResolver->isValue($typeValue, 'collection');
    }
}
