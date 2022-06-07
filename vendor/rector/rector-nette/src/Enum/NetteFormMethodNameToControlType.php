<?php

declare (strict_types=1);
namespace Rector\Nette\Enum;

final class NetteFormMethodNameToControlType
{
    /**
     * @var array<string, string>
     */
    public const METHOD_NAME_TO_CONTROL_TYPE = [
        'addText' => 'RectorPrefix20220607\\Nette\\Forms\\Controls\\TextInput',
        'addPassword' => 'RectorPrefix20220607\\Nette\\Forms\\Controls\\TextInput',
        'addEmail' => 'RectorPrefix20220607\\Nette\\Forms\\Controls\\TextInput',
        'addInteger' => 'RectorPrefix20220607\\Nette\\Forms\\Controls\\TextInput',
        'addUpload' => 'RectorPrefix20220607\\Nette\\Forms\\Controls\\UploadControl',
        'addMultiUpload' => 'RectorPrefix20220607\\Nette\\Forms\\Controls\\UploadControl',
        'addTextArea' => 'RectorPrefix20220607\\Nette\\Forms\\Controls\\TextArea',
        'addHidden' => 'RectorPrefix20220607\\Nette\\Forms\\Controls\\HiddenField',
        'addCheckbox' => 'RectorPrefix20220607\\Nette\\Forms\\Controls\\Checkbox',
        'addRadioList' => 'RectorPrefix20220607\\Nette\\Forms\\Controls\\RadioList',
        'addCheckboxList' => 'RectorPrefix20220607\\Nette\\Forms\\Controls\\CheckboxList',
        'addSelect' => 'RectorPrefix20220607\\Nette\\Forms\\Controls\\SelectBox',
        'addMultiSelect' => 'RectorPrefix20220607\\Nette\\Forms\\Controls\\MultiSelectBox',
        'addSubmit' => 'RectorPrefix20220607\\Nette\\Forms\\Controls\\SubmitButton',
        'addButton' => 'RectorPrefix20220607\\Nette\\Forms\\Controls\\Button',
        'addImage' => 'RectorPrefix20220607\\Nette\\Forms\\Controls\\ImageButton',
        // custom
        'addJSelect' => 'RectorPrefix20220607\\DependentSelectBox\\JsonDependentSelectBox',
    ];
}
