<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

final class AddRuleCall extends Form
{
    public function __construct()
    {
        $this->addText('phone_number', 'Phone number')
            ->addRule(function (Form $control) {
                return $this->parent instanceof SomeControl
                    ? $this->parent->isPhoneNumberValid($control->getValue())
                    : true;
            }, 'Invalid phone number.')
            ->setRequired();

        $this->addText('code', 'verify');

        $this['code']->addConditionOn($this['phone_number'], Form::EQUAL, 2)
            ->setRequired();
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

final class AddRuleCall extends Form
{
    public function __construct()
    {
        $this->addText('phone_number', 'Phone number')
            ->addRule(function (Form $control) {
                return $this->parent instanceof SomeControl
                    ? $this->parent->isPhoneNumberValid($control->getValue())
                    : true;
            }, 'Invalid phone number.')
            ->setRequired();

        $this->addText('code', 'verify');
        /** @var \Nette\Forms\Controls\TextInput $codeControl */
        $codeControl = $this['code'];
        /** @var \Nette\Forms\Controls\TextInput $phoneNumberControl */
        $phoneNumberControl = $this['phone_number'];

        $codeControl->addConditionOn($phoneNumberControl, Form::EQUAL, 2)
            ->setRequired();
    }
}

?>
