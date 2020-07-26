<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\ValueObject;

final class NetteFormMethodNameToControlType
{
    /**
     * @var string[]
     */
    public const METHOD_NAME_TO_CONTROL_TYPE = [
        'Nette\Forms\Controls\TextInput' => ['addText', 'addPassword', 'addEmail', 'addInteger'],
        'Nette\Forms\Controls\TextArea' => ['addTextArea'],
        'Nette\Forms\Controls\UploadControl' => ['addUpload', 'addMultiUpload'],
        'Nette\Forms\Controls\HiddenField' => ['addHidden'],
        'Nette\Forms\Controls\Checkbox' => ['addCheckbox'],
        'Nette\Forms\Controls\RadioList' => ['addRadioList'],
        'Nette\Forms\Controls\CheckboxList' => ['addCheckboxList'],
        'Nette\Forms\Controls\SelectBox' => ['addSelect'],
        'Nette\Forms\Controls\MultiSelectBox' => ['addMultiSelect'],
        'Nette\Forms\Controls\SubmitButton' => ['addSubmit'],
        'Nette\Forms\Controls\Button' => ['addButton'],
        'Nette\Forms\Controls\ImageButton' => ['addImage'],
    ];
}
