<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer;
use Rector\Symfony\NodeAnalyzer\FormCollectionAnalyzer;
use Rector\Symfony\NodeAnalyzer\FormOptionsArrayMatcher;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use ReflectionMethod;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Best resource with clear example:
 *
 * @see https://stackoverflow.com/questions/34027711/passing-data-to-buildform-in-symfony-2-8-3-0
 *
 * @see https://github.com/symfony/symfony/commit/adf20c86fb0d8dc2859aa0d2821fe339d3551347
 * @see http://www.keganv.com/passing-arguments-controller-file-type-symfony-3/
 * @see https://github.com/symfony/symfony/blob/2.8/UPGRADE-2.8.md#form
 *
 * @see \Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\FormTypeInstanceToClassConstRectorTest
 */
final class FormTypeInstanceToClassConstRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer
     */
    private $formAddMethodCallAnalyzer;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\FormOptionsArrayMatcher
     */
    private $formOptionsArrayMatcher;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\FormCollectionAnalyzer
     */
    private $formCollectionAnalyzer;
    /**
     * @readonly
     * @var \Rector\Symfony\TypeAnalyzer\ControllerAnalyzer
     */
    private $controllerAnalyzer;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Symfony\NodeAnalyzer\FormAddMethodCallAnalyzer $formAddMethodCallAnalyzer, \Rector\Symfony\NodeAnalyzer\FormOptionsArrayMatcher $formOptionsArrayMatcher, \Rector\Symfony\NodeAnalyzer\FormCollectionAnalyzer $formCollectionAnalyzer, \Rector\Symfony\TypeAnalyzer\ControllerAnalyzer $controllerAnalyzer)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->formAddMethodCallAnalyzer = $formAddMethodCallAnalyzer;
        $this->formOptionsArrayMatcher = $formOptionsArrayMatcher;
        $this->formCollectionAnalyzer = $formCollectionAnalyzer;
        $this->controllerAnalyzer = $controllerAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes createForm(new FormType), add(new FormType) to ones with "FormType::class"', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeController
{
    public function action()
    {
        $form = $this->createForm(new TeamType, $entity);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeController
{
    public function action()
    {
        $form = $this->createForm(TeamType::class, $entity);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->controllerAnalyzer->isController($node->var) && $this->isName($node->name, 'createForm')) {
            return $this->processNewInstance($node, 0, 2);
        }
        if (!$this->formAddMethodCallAnalyzer->isMatching($node)) {
            return null;
        }
        // special case for collections
        if ($this->formCollectionAnalyzer->isCollectionType($node)) {
            $this->refactorCollectionOptions($node);
        }
        return $this->processNewInstance($node, 1, 2);
    }
    private function processNewInstance(\PhpParser\Node\Expr\MethodCall $methodCall, int $position, int $optionsPosition) : ?\PhpParser\Node
    {
        if (!isset($methodCall->args[$position])) {
            return null;
        }
        $argValue = $methodCall->getArgs()[$position]->value;
        if (!$argValue instanceof \PhpParser\Node\Expr\New_) {
            return null;
        }
        // we can only process direct name
        if (!$argValue->class instanceof \PhpParser\Node\Name) {
            return null;
        }
        if ($argValue->args !== []) {
            $methodCall = $this->moveArgumentsToOptions($methodCall, $position, $optionsPosition, $argValue->class->toString(), $argValue->getArgs());
            if (!$methodCall instanceof \PhpParser\Node\Expr\MethodCall) {
                return null;
            }
        }
        $currentArg = $methodCall->getArgs()[$position];
        $classConstReference = $this->nodeFactory->createClassConstReference($argValue->class->toString());
        $currentArg->value = $classConstReference;
        return $methodCall;
    }
    private function refactorCollectionOptions(\PhpParser\Node\Expr\MethodCall $methodCall) : void
    {
        $optionsArray = $this->formOptionsArrayMatcher->match($methodCall);
        if (!$optionsArray instanceof \PhpParser\Node\Expr\Array_) {
            return;
        }
        foreach ($optionsArray->items as $arrayItem) {
            if ($arrayItem === null) {
                continue;
            }
            if ($arrayItem->key === null) {
                continue;
            }
            if (!$this->valueResolver->isValues($arrayItem->key, ['entry', 'entry_type'])) {
                continue;
            }
            if (!$arrayItem->value instanceof \PhpParser\Node\Expr\New_) {
                continue;
            }
            $newClass = $arrayItem->value->class;
            if (!$newClass instanceof \PhpParser\Node\Name) {
                continue;
            }
            $arrayItem->value = $this->nodeFactory->createClassConstReference($newClass->toString());
        }
    }
    /**
     * @param Arg[] $argNodes
     */
    private function moveArgumentsToOptions(\PhpParser\Node\Expr\MethodCall $methodCall, int $position, int $optionsPosition, string $className, array $argNodes) : ?\PhpParser\Node\Expr\MethodCall
    {
        $namesToArgs = $this->resolveNamesToArgs($className, $argNodes);
        // set default data in between
        if ($position + 1 !== $optionsPosition && !isset($methodCall->args[$position + 1])) {
            $methodCall->args[$position + 1] = new \PhpParser\Node\Arg($this->nodeFactory->createNull());
        }
        // @todo decopule and name, so I know what it is
        if (!isset($methodCall->args[$optionsPosition])) {
            $array = new \PhpParser\Node\Expr\Array_();
            foreach ($namesToArgs as $name => $arg) {
                $array->items[] = new \PhpParser\Node\Expr\ArrayItem($arg->value, new \PhpParser\Node\Scalar\String_($name));
            }
            $methodCall->args[$optionsPosition] = new \PhpParser\Node\Arg($array);
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $formTypeClassReflection = $this->reflectionProvider->getClass($className);
        if (!$formTypeClassReflection->hasConstructor()) {
            return null;
        }
        // nothing we can do, out of scope
        return $methodCall;
    }
    /**
     * @param Arg[] $args
     * @return array<string, Arg>
     */
    private function resolveNamesToArgs(string $className, array $args) : array
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return [];
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $reflectionClass = $classReflection->getNativeReflection();
        $constructorReflectionMethod = $reflectionClass->getConstructor();
        if (!$constructorReflectionMethod instanceof \ReflectionMethod) {
            return [];
        }
        $namesToArgs = [];
        foreach ($constructorReflectionMethod->getParameters() as $position => $reflectionParameter) {
            $namesToArgs[$reflectionParameter->getName()] = $args[$position];
        }
        return $namesToArgs;
    }
}
