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
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\Manipulator\MethodCallManipulator;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeCollector\NodeFinder\FunctionLikeParsedNodesFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

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

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        BetterNodeFinder $betterNodeFinder,
        MethodCallManipulator $methodCallManipulator,
        ValueResolver $valueResolver,
        FunctionLikeParsedNodesFinder $functionLikeParsedNodesFinder,
        BetterStandardPrinter $betterStandardPrinter,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->methodCallManipulator = $methodCallManipulator;
        $this->valueResolver = $valueResolver;
        $this->functionLikeParsedNodesFinder = $functionLikeParsedNodesFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeTypeResolver = $nodeTypeResolver;
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
        /** @var Assign|null $formVariableAssign */
        $formVariableAssign = $this->findPreviousAssignToVariable($formVariable);
        if ($formVariableAssign === null) {
            return [];
        }

        if ($formVariableAssign->expr instanceof New_) {
            return $this->resolveFromNew($formVariableAssign->expr);
        }

        if ($formVariableAssign->expr instanceof MethodCall) {
            return $this->resolveFromMethodCall($formVariableAssign->expr);
        }

        return [];
    }

    private function resolveFromMethodCall(MethodCall $methodCall): array
    {
        if ($this->nodeNameResolver->isName($methodCall->name, 'getComponent')) {
            return $this->resolveFromGetComponentMethodCall($methodCall);
        }

        $classMethod = $this->functionLikeParsedNodesFinder->findClassMethodByMethodCall($methodCall);

        if ($classMethod === null) {
            return [];
        }

        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        // magic Nette factory, continue to return type contructor
        if ($classLike instanceof Interface_) {
            $returnedType = $this->nodeTypeResolver->getStaticType($methodCall);
            if ($returnedType instanceof TypeWithClassName) {
                $constructorClassMethod = $this->functionLikeParsedNodesFinder->findClassMethod(
                    self::CONSTRUCT,
                    $returnedType->getClassName()
                );
                if ($constructorClassMethod === null) {
                    return [];
                }

                return $this->resolveFromConstructorClassMethod($constructorClassMethod);
            }

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

        $initialAssignMethodNamesByInputNames = $this->resolveInitialVariableAssignMethodNamesByInputNames($lastReturn);
        $previousMethodCallMethodNamesByInputNames = $this->resolveMethodNamesByInputNames($lastReturn->expr);

        return array_merge($initialAssignMethodNamesByInputNames, $previousMethodCallMethodNamesByInputNames);
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

    private function findPreviousAssignToVariable(Variable $variable): ?Assign
    {
        return $this->betterNodeFinder->findFirstPrevious($variable, function (Node $node) use ($variable) {
            if (! $node instanceof Assign) {
                return false;
            }

            return $this->betterStandardPrinter->areNodesEqual($node->var, $variable);
        });
    }

    /**
     * @return array<string, string>
     */
    private function resolveInitialVariableAssignMethodNamesByInputNames(Return_ $return): array
    {
        if (! $return->expr instanceof Variable) {
            return [];
        }

        $initialAssign = $this->findPreviousAssignToVariable($return->expr);
        if (! $initialAssign instanceof Assign) {
            return [];
        }

        if ($initialAssign->expr instanceof MethodCall) {
            return $this->resolveFromMethodCall($initialAssign->expr);
        }

        if ($initialAssign->expr instanceof New_) {
            return $this->resolveFromNew($initialAssign->expr);
        }

        return [];
    }

    /**
     * @return array<string, string>
     */
    private function resolveFromGetComponentMethodCall(MethodCall $methodCall): array
    {
        $createComponentClassMethodName = 'createComponent' . ucfirst(
            $this->valueResolver->getValue($methodCall->args[0]->value)
        );

        $externalMethodNamesByInputsNames = [];

        $staticType = $this->nodeTypeResolver->getStaticType($methodCall);
        if ($staticType instanceof FullyQualifiedObjectType) {
            // combine constructor + method body name
            $constructorClassMethod = $this->functionLikeParsedNodesFinder->findClassMethod(
                self::CONSTRUCT,
                $staticType->getClassName()
            );

            if ($constructorClassMethod !== null) {
                $collectedList = $this->resolveFromConstructorClassMethod($constructorClassMethod);
                $externalMethodNamesByInputsNames = array_merge($externalMethodNamesByInputsNames, $collectedList);
            }
        }

        $callerType = $this->nodeTypeResolver->getStaticType($methodCall->var);

        if ($callerType instanceof TypeWithClassName) {
            $createComponentClassMethod = $this->functionLikeParsedNodesFinder->findClassMethod(
                $createComponentClassMethodName,
                $callerType->getClassName()
            );
            if ($createComponentClassMethod !== null) {
                $collectedList = $this->resolveFromClassMethod($createComponentClassMethod);
                $externalMethodNamesByInputsNames = array_merge($externalMethodNamesByInputsNames, $collectedList);
            }
        }

        return $externalMethodNamesByInputsNames;
    }
}
