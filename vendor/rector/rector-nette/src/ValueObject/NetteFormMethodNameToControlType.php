<?php

declare (strict_types=1);
namespace Rector\Nette\ValueObject;

use RectorPrefix20211020\Nette\Forms\Controls\BaseControl;
final class NetteFormMethodNameToControlType
{
    /**
     * @var array<string, class-string<BaseControl>>
     */
    public const METHOD_NAME_TO_CONTROL_TYPE = [
        'addText' => 'Nette\\Forms\\Controls\\TextInput',
        'addPassword' => 'Nette\\Forms\\Controls\\TextInput',
        'addEmail' => 'Nette\\Forms\\Controls\\TextInput',
        'addInteger' => 'Nette\\Forms\\Controls\\TextInput',
        'addUpload' => 'Nette\\Forms\\Controls\\UploadControl',
        'addMultiUpload' => 'Nette\\Forms\\Controls\\UploadControl',
        'addTextArea' => 'Nette\\Forms\\Controls\\TextArea',
        'addHidden' => 'Nette\\Forms\\Controls\\HiddenField',
        'addCheckbox' => 'Nette\\Forms\\Controls\\Checkbox',
        'addRadioList' => 'Nette\\Forms\\Controls\\RadioList',
        'addCheckboxList' => 'Nette\\Forms\\Controls\\CheckboxList',
        'addSelect' => 'Nette\\Forms\\Controls\\SelectBox',
        'addMultiSelect' => 'Nette\\Forms\\Controls\\MultiSelectBox',
        'addSubmit' => 'Nette\\Forms\\Controls\\SubmitButton',
        'addButton' => 'Nette\\Forms\\Controls\\Button',
        'addImage' => 'Nette\\Forms\\Controls\\ImageButton',
        // custom
        'addJSelect' => 'DependentSelectBox\\JsonDependentSelectBox',
    ];
}
