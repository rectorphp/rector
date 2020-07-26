<?php

declare(strict_types=1);

namespace Rector\Nette\NodeResolver;

use PhpParser\Node\Expr;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Exception\ShouldNotHappenException;

final class FormVariableInputNameTypeResolver
{
    /**
     * @var string[][]
     */
    private const METHOD_NAMES_BY_CONTROL_TYPE = [
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

    /**
     * @var MethodNamesByInputNamesResolver
     */
    private $methodNamesByInputNamesResolver;

    public function __construct(MethodNamesByInputNamesResolver $methodNamesByInputNamesResolver)
    {
        $this->methodNamesByInputNamesResolver = $methodNamesByInputNamesResolver;
    }

    public function resolveControlTypeByInputName(Expr $formOrControlExpr, string $inputName): string
    {
        $methodNamesByInputNames = $this->methodNamesByInputNamesResolver->resolveExpr($formOrControlExpr);

        $formAddMethodName = $methodNamesByInputNames[$inputName] ?? null;
        if ($formAddMethodName === null) {
            $message = sprintf('Type was not found for "%s" input name', $inputName);
            throw new ShouldNotHappenException($message);
        }

        foreach (self::METHOD_NAMES_BY_CONTROL_TYPE as $controlType => $methodNames) {
            if (! in_array($formAddMethodName, $methodNames, true)) {
                continue;
            }

            return $controlType;
        }

        throw new NotImplementedYetException();
    }
}
