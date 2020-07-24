<?php

declare(strict_types=1);

namespace Rector\Nette\NodeResolver;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\Manipulator\MethodCallManipulator;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeCollector\NodeFinder\FunctionLikeParsedNodesFinder;
use Rector\NodeNameResolver\NodeNameResolver;

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
     * @var string
     */
    private const CONSTRUCT = '__construct';

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var MethodCallManipulator
     */
    private $methodCallManipulator;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var FunctionLikeParsedNodesFinder
     */
    private $functionLikeParsedNodesFinder;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        BetterNodeFinder $betterNodeFinder,
        MethodCallManipulator $methodCallManipulator,
        ValueResolver $valueResolver,
        FunctionLikeParsedNodesFinder $functionLikeParsedNodesFinder,
        BetterStandardPrinter $betterStandardPrinter
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->methodCallManipulator = $methodCallManipulator;
        $this->valueResolver = $valueResolver;
        $this->functionLikeParsedNodesFinder = $functionLikeParsedNodesFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    public function resolveControlTypeByInputName(Variable $formVariable, string $inputName): string
    {
        $localMethodNamesByInputNames = $this->resolveMethodNamesByInputNames($formVariable);

        // 1. find first node assign → if is method call, enter it
        $externalMethodNamesByInputsNames = $this->resolveExternalMethodNamesByInputNames($formVariable);

        $methodNamesByInputNames = array_merge($localMethodNamesByInputNames, $externalMethodNamesByInputsNames);
        $formAddMethodName = $methodNamesByInputNames[$inputName] ?? null;
        if ($formAddMethodName === null) {
            $message = sprintf('Not found for "%s" input name', $inputName);
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

    /**
     * @return array<string, string>
     */
    private function resolveMethodNamesByInputNames(Variable $formVariable): array
    {
        $onFormMethodCalls = $this->methodCallManipulator->findMethodCallsOnVariable($formVariable);

        $methodNamesByInputNames = [];
        foreach ($onFormMethodCalls as $onFormMethodCall) {
            $methodName = $this->nodeNameResolver->getName($onFormMethodCall->name);
            if ($methodName === null) {
                continue;
            }

            if (! Strings::startsWith($methodName, 'add')) {
                continue;
            }

            if (! isset($onFormMethodCall->args[0])) {
                continue;
            }

            $addedInputName = $this->valueResolver->getValue($onFormMethodCall->args[0]->value);
            if (! is_string($addedInputName)) {
                throw new ShouldNotHappenException();
            }

            $methodNamesByInputNames[$addedInputName] = $methodName;
        }

        return $methodNamesByInputNames;
    }

    /**
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

            return $this->betterStandardPrinter->areNodesEqual($node->var, $formVariable);
        });

        if ($formVariableAssignNode === null) {
            return [];
        }

        if ($formVariableAssignNode->expr instanceof New_) {
            return $this->resolveFromNew($formVariableAssignNode->expr);
        }

        if ($formVariableAssignNode->expr instanceof MethodCall) {
            return $this->resolveFromMethodCall($formVariableAssignNode->expr);
        }

        return [];
    }

    private function resolveFromMethodCall(MethodCall $methodCall): array
    {
        $classMethod = $this->functionLikeParsedNodesFinder->findClassMethodByMethodCall($methodCall);
        if ($classMethod === null) {
            return [];
        }
        return $this->resolveFromClassMethod($classMethod);
    }

    /**
     * @return array<string, string>
     */
    private function resolveFromNew(New_ $new): array
    {
        $className = $this->nodeNameResolver->getName($new->class);
        if ($className === null) {
            return [];
        }

        $constructorClassMethod = $this->functionLikeParsedNodesFinder->findClassMethod(self::CONSTRUCT, $className);
        if ($constructorClassMethod === null) {
            return [];
        }

        return $this->resolveFromConstructorClassMethod($constructorClassMethod);
    }

    /**
     * @return string[]
     */
    private function resolveFromClassMethod(ClassMethod $classMethod): array
    {
        // 1. find last return
        /** @var Return_|null $lastReturn */
        $lastReturn = $this->betterNodeFinder->findLastInstanceOf((array) $classMethod->stmts, Return_::class);
        if ($lastReturn === null) {
            return [];
        }

        if (! $lastReturn->expr instanceof Variable) {
            return [];
        }

        return $this->resolveMethodNamesByInputNames($lastReturn->expr);
    }

    /**
     * @return string[]
     */
    private function resolveFromConstructorClassMethod(ClassMethod $constructorClassMethod): array
    {
        /** @var Variable|null $thisVariable */
        $thisVariable = $this->betterNodeFinder->findVariableOfName($constructorClassMethod, 'this');
        if ($thisVariable === null) {
            return [];
        }

        return $this->resolveMethodNamesByInputNames($thisVariable);
    }
}
