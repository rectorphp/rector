<?php

declare (strict_types=1);
namespace Rector\NetteToSymfony\ValueObject;

final class NetteFormMethodToSymfonyTypeClass
{
    /**
     * @var array<string, class-string>
     */
    public const ADD_METHOD_TO_FORM_TYPE = [
        'addText' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\TextType',
        'addPassword' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\PasswordType',
        'addTextArea' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\TextareaType',
        'addEmail' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\EmailType',
        'addInteger' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\IntegerType',
        'addHidden' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\HiddenType',
        // https://symfony.com/doc/current/reference/forms/types/checkbox.html
        'addCheckbox' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\CheckboxType',
        'addUpload' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\FileType',
        'addImage' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\FileType',
        'addMultiUpload' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\FileType',
        // https://symfony.com/doc/current/reference/forms/types/choice.html#select-tag-checkboxes-or-radio-buttons
        'addSelect' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType',
        'addRadioList' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType',
        'addCheckboxList' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType',
        'addMultiSelect' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType',
        'addSubmit' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\SubmitType',
        'addButton' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\ButtonType',
    ];
}
