<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Symfony\NodeAnalyzer\FormType\CreateFormTypeOptionsArgMover;
use Rector\Symfony\NodeAnalyzer\FormType\FormTypeClassResolver;
use ReflectionMethod;
final class FormInstanceToFormClassConstFetchConverter
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\FormType\CreateFormTypeOptionsArgMover
     */
    private $createFormTypeOptionsArgMover;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\FormType\FormTypeClassResolver
     */
    private $formTypeClassResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(CreateFormTypeOptionsArgMover $createFormTypeOptionsArgMover, NodeFactory $nodeFactory, FormTypeClassResolver $formTypeClassResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->createFormTypeOptionsArgMover = $createFormTypeOptionsArgMover;
        $this->nodeFactory = $nodeFactory;
        $this->formTypeClassResolver = $formTypeClassResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function processNewInstance(MethodCall $methodCall, int $position, int $optionsPosition) : ?MethodCall
    {
        $args = $methodCall->getArgs();
        if (!isset($args[$position])) {
            return null;
        }
        $argValue = $args[$position]->value;
        $formClassName = $this->formTypeClassResolver->resolveFromExpr($argValue);
        if ($formClassName === null) {
            return null;
        }
        // better skip and ahndle manualyl
        if ($argValue instanceof Variable && $this->isVariableOfTypeWithRequiredConstructorParmaeters($argValue)) {
            return null;
        }
        if ($argValue instanceof New_ && $argValue->getArgs() !== []) {
            $methodCall = $this->createFormTypeOptionsArgMover->moveArgumentsToOptions($methodCall, $position, $optionsPosition, $formClassName, $argValue->getArgs());
            if (!$methodCall instanceof MethodCall) {
                throw new ShouldNotHappenException();
            }
        }
        $classConstFetch = $this->nodeFactory->createClassConstReference($formClassName);
        $currentArg = $methodCall->getArgs()[$position];
        $currentArg->value = $classConstFetch;
        return $methodCall;
    }
    private function isVariableOfTypeWithRequiredConstructorParmaeters(Variable $variable) : bool
    {
        // if form type is object with constructor args, handle manually
        $variableType = $this->nodeTypeResolver->getType($variable);
        if (!$variableType instanceof TypeWithClassName) {
            return \false;
        }
        $classReflection = $variableType->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        if (!$classReflection->hasConstructor()) {
            return \false;
        }
        $nativeReflection = $classReflection->getNativeReflection();
        $reflectionMethod = $nativeReflection->getConstructor();
        if (!$reflectionMethod instanceof ReflectionMethod) {
            return \false;
        }
        return $reflectionMethod->getNumberOfRequiredParameters() > 0;
    }
}
