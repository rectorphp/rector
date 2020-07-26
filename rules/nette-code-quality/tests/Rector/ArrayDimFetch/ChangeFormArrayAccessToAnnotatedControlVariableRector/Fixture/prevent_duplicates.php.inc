<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class PreventDuplicates extends Form
{
    public function __construct()
    {
        $this->addTextArea('text', 'Text');
        $this->addUpload('file', 'Attachment')
            ->addRule(Form::IMAGE)
            ->setRequired(false);

        $this['text']->addConditionOn($this['file'], Form::BLANK)->setRequired();
        $this['file']->getControlPrototype()->accept = 'image/*';
    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\Fixture;

use Nette\Application\UI\Form;

class PreventDuplicates extends Form
{
    public function __construct()
    {
        $this->addTextArea('text', 'Text');
        $this->addUpload('file', 'Attachment')
            ->addRule(Form::IMAGE)
            ->setRequired(false);
        /** @var \Nette\Forms\Controls\TextArea $textControl */
        $textControl = $this['text'];
        /** @var \Nette\Forms\Controls\UploadControl $fileControl */
        $fileControl = $this['file'];

        $textControl->addConditionOn($fileControl, Form::BLANK)->setRequired();
        $fileControl->getControlPrototype()->accept = 'image/*';
    }
}

?>
