<?php declare (strict_types=1);

namespace {
    class SomePresenter
    {
        public function createNetteForm()
        {
            $form = new Stub_Nette\Application\UI\Form;
            $form->addText('name')
                ->addCondition($form::FILLED)
                ->addRule('...');

            return $form;
        }
    }
}

// stubs with 1:1 behavior - only the relevant methods and types
// to remove dependency on Nette classes

namespace Stub_Nette\Application\UI
{
    use Stub_Nette\Forms\Form as BaseForm;
    use Stub_Nette\Forms\Controls\TextInput;

    class Form extends BaseForm {
        const FILLED = 'filled';
        public function addText($name): TextInput
        {}
    }
}

namespace Stub_Nette\Forms\Controls
{

    use Stub_Nette\Forms\Rules;

    class TextArea
    {
        public function addCondition($rule): Rules
        {}
    }

    class TextInput extends TextArea
    {

    }
}

namespace Stub_Nette\Forms
{
    class Rules
    {
        public function addRule($name): self
        {
        }
    }

    class Form
    {
    }
}
