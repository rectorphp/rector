<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\ArrayDimFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Manipulator\MethodCallManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeCollector\NodeFinder\FunctionLikeParsedNodesFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Nette\Tests\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector\ChangeFormArrayAccessToAnnotatedControlVariableRectorTest
 */
final class ChangeFormArrayAccessToAnnotatedControlVariableRector extends AbstractRector
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
     * @var MethodCallManipulator
     */
    private $methodCallManipulator;

    public function __construct(
        MethodCallManipulator $methodCallManipulator,
        FunctionLikeParsedNodesFinder $functionLikeParsedNodesFinder
    ) {
        $this->methodCallManipulator = $methodCallManipulator;
        $this->functionLikeParsedNodesFinder = $functionLikeParsedNodesFinder;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change array access magic on $form to explicit standalone typed variable', [
            new CodeSample(
                <<<'PHP'
use Nette\Application\UI\Form;

class SomePresenter
{
    public function run()
    {
        $form = new Form();
        $this->addText('email', 'Email');

        $form['email']->value = 'hey@hi.hello';
    }
}
PHP
,
                <<<'PHP'
use Nette\Application\UI\Form;

class SomePresenter
{
    public function run()
    {
        $form = new Form();
        $this->addText('email', 'Email');

        /** @var \Nette\Forms\Controls\BaseControl $emailControl */
        $emailControl = $form['email'];
        $emailControl->value = 'hey@hi.hello';
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
        return [ArrayDimFetch::class];
    }

    /**
     * @param ArrayDimFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        $dimString = $this->matchNetteFormArrayDimString($node);
        if ($dimString === null) {
            return null;
        }

        if ($this->isBeingAssigned($node)) {
            return null;
        }

        $inputName = $this->getValue($dimString);
        $controlVariableName = StaticRectorStrings::underscoreToPascalCase($inputName) . 'Control';

        $controlVariableToFormDimFetchAssign = new Assign(new Variable($controlVariableName), clone $node);
        $assignExpression = new Expression($controlVariableToFormDimFetchAssign);

        // 1. find previous calls on variable
        /** @var Variable $formVariable */
        $formVariable = $node->var;

        $controlType = $this->resolveControlTypeByInputName($formVariable, $inputName);
        $this->addVarTag($controlVariableToFormDimFetchAssign, $assignExpression, $controlVariableName, $controlType);

        $this->addNodeBeforeNode($assignExpression, $node);

        return new Variable($controlVariableName);
    }

    private function addVarTag(
        Assign $assign,
        Expression $assignExpression,
        string $controlName,
        string $controlType
    ): PhpDocInfo {
        $phpDocInfo = $this->phpDocInfoFactory->createEmpty($assignExpression);
        $identifierTypeNode = new IdentifierTypeNode('\\' . $controlType);

        $varTagValueNode = new VarTagValueNode($identifierTypeNode, '$' . $controlName, '');
        $phpDocInfo->addTagValueNode($varTagValueNode);
        $phpDocInfo->makeSingleLined();

        $assign->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);

        return $phpDocInfo;
    }

    private function matchNetteFormArrayDimString($node): ?String_
    {
        if (! $node->var instanceof Variable) {
            return null;
        }

        if (! $this->isObjectType($node->var, 'Nette\Application\UI\Form')) {
            return null;
        }

        if (! $node->dim instanceof String_) {
            return null;
        }

        return $node->dim;
    }

    private function isBeingAssigned(ArrayDimFetch $arrayDimFetch): bool
    {
        $parent = $arrayDimFetch->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Assign) {
            return false;
        }

        return $parent->expr === $arrayDimFetch;
    }

    private function resolveControlTypeByInputName(Variable $formVariable, string $inputName): string
    {
        $localMethodNamesByInputNames = $this->resolveMethodNamesByInputNames($formVariable);

        // 1. find first node assign → if is method call, enter it
        $externalMethodNamesByInputsNames = $this->resolveExternalMethodNamesByInputNames($formVariable);

        $methodNamesByInputNames = array_merge($localMethodNamesByInputNames, $externalMethodNamesByInputsNames);

        $formAddMethodName = $methodNamesByInputNames[$inputName] ?? null;
        if ($formAddMethodName === null) {
            throw new NotImplementedYetException();
        }

        foreach (self::METHOD_NAMES_BY_CONTROL_TYPE as $controlType => $methodNames) {
            if (! in_array($formAddMethodName, $methodNames, true)) {
                continue;
            }

            return $controlType;
        }

        throw new NotImplementedYetException();
    }

    /**
     * @todo move to MethodCallManipulator
     * @return array<string, string>
     */
    private function resolveMethodNamesByInputNames(Variable $formVariable): array
    {
        $onFormMethodCalls = $this->methodCallManipulator->findMethodCallsOnVariable($formVariable);

        $methodNamesByInputNames = [];
        foreach ($onFormMethodCalls as $onFormMethodCall) {
            if (! $this->isName($onFormMethodCall->name, 'add*')) {
                continue;
            }

            if (! isset($onFormMethodCall->args[0])) {
                continue;
            }

            $addedInputName = $this->getValue($onFormMethodCall->args[0]->value);
            if ($addedInputName === null) {
                throw new ShouldNotHappenException();
            }

            $methodName = $this->getName($onFormMethodCall->name);
            if ($methodName === null) {
                throw new ShouldNotHappenException();
            }

            $methodNamesByInputNames[$addedInputName] = $methodName;
        }

        return $methodNamesByInputNames;
    }

    /**
     * @todo move to MethodCallManipulator
     * @return string[]
     */
    private function resolveExternalMethodNamesByInputNames(Variable $formVariable): array
    {
        /** @var Assign|null $formVariableAssignNode */
        $formVariableAssignNode = $this->betterNodeFinder->findFirstPrevious($formVariable, function (Node $node) use (
            $formVariable
        ) {
            if (! $node instanceof Assign) {
                return false;
            }

            return $this->areNodesEqual($node->var, $formVariable);
        });

        if ($formVariableAssignNode === null) {
            return [];
        }

        if (! $formVariableAssignNode->expr instanceof MethodCall) {
            return [];
        }

        $classMethod = $this->functionLikeParsedNodesFinder->findClassMethodByMethodCall($formVariableAssignNode->expr);
        if ($classMethod === null) {
            return [];
        }

        // 1. find last return
        /** @var Return_|null $lastReturn */
        $lastReturn = $this->betterNodeFinder->findLastInstanceOf((array) $classMethod->stmts, Return_::class);
        if ($lastReturn === null) {
            return [];
        }

        if ($lastReturn->expr === null) {
            return [];
        }

        if (! $lastReturn->expr instanceof Variable) {
            return [];
        }

        return $this->resolveMethodNamesByInputNames($lastReturn->expr);
    }
}
