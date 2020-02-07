<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\MethodCall;

use Nette\Application\UI\Form;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Nette\Tests\Rector\MethodCall\AddDatePickerToDateControlRector\AddDatePickerToDateControlRectorTest
 */
final class AddDatePickerToDateControlRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Nextras/Form upgrade of addDatePicker method call to DateControl assign', [
            new CodeSample(
                <<<'PHP'
use Nette\Application\UI\Form;

class SomeClass
{
    public function run()
    {
        $form = new Form();
        $form->addDatePicker('key', 'Label');
    }
}
PHP
,
                <<<'PHP'
use Nette\Application\UI\Form;

class SomeClass
{
    public function run()
    {
        $form = new Form();
        $form['key'] = new \Nextras\FormComponents\Controls\DateControl('Label');
    }
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node->name, 'addDatePicker')) {
            return null;
        }

        if (! $this->isObjectType($node->var, Form::class)) {
            return null;
        }

        $key = $node->args[0]->value;
        $leftVariable = new ArrayDimFetch($node->var, $key);

        $dateControlClass = new FullyQualified('Nextras\FormComponents\Controls\DateControl');
        $newDateControl = new New_($dateControlClass);

        if (isset($node->args[1])) {
            $newDateControl->args[] = $node->args[1];
        }

        return new Assign($leftVariable, $newDateControl);
    }
}
