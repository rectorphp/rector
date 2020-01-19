<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @see https://github.com/symfony/symfony/commit/adf20c86fb0d8dc2859aa0d2821fe339d3551347
 * @see http://www.keganv.com/passing-arguments-controller-file-type-symfony-3/
 * @see https://stackoverflow.com/questions/34027711/passing-data-to-buildform-in-symfony-2-8-3-0
 * @see https://github.com/symfony/symfony/blob/2.8/UPGRADE-2.8.md#form
 *
 * @see \Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\FormTypeInstanceToClassConstRectorTest
 */
final class FormTypeInstanceToClassConstRector extends AbstractRector
{
    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    public function __construct(ParsedNodesByType $parsedNodesByType)
    {
        $this->parsedNodesByType = $parsedNodesByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes createForm(new FormType), add(new FormType) to ones with "FormType::class"',
            [
                new CodeSample(
                    <<<'PHP'
class SomeController
{
    public function action()
    {
        $form = $this->createForm(new TeamType, $entity, [
            'action' => $this->generateUrl('teams_update', ['id' => $entity->getId()]),
            'method' => 'PUT',
        ]);
    }
}
PHP
                    ,
                    <<<'PHP'
class SomeController
{
    public function action()
    {
        $form = $this->createForm(TeamType::class, $entity, [
            'action' => $this->generateUrl('teams_update', ['id' => $entity->getId()]),
            'method' => 'PUT',
        ]);
    }
}
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isObjectType($node->var, Controller::class) && $this->isName($node->name, 'createForm')) {
            return $this->processNewInstance($node, 0, 2);
        }

        if (! $this->isObjectTypes($node->var, [FormBuilderInterface::class, FormInterface::class])) {
            return null;
        }

        if (! $this->isName($node->name, 'add')) {
            return null;
        }

        return $this->processNewInstance($node, 1, 2);
    }

    private function processNewInstance(MethodCall $methodCall, int $position, int $optionsPosition): ?Node
    {
        if (! isset($methodCall->args[$position])) {
            return null;
        }

        if (! $methodCall->args[$position]->value instanceof New_) {
            return null;
        }

        /** @var New_ $newNode */
        $newNode = $methodCall->args[$position]->value;

        // we can only process direct name
        if (! $newNode->class instanceof Name) {
            return null;
        }

        if (count($newNode->args) > 0) {
            $methodCall = $this->moveArgumentsToOptions(
                $methodCall,
                $position,
                $optionsPosition,
                $newNode->class->toString(),
                $newNode->args
            );

            if ($methodCall === null) {
                return null;
            }
        }

        $methodCall->args[$position]->value = new ClassConstFetch($newNode->class, 'class');

        return $methodCall;
    }

    /**
     * @param Arg[] $argNodes
     */
    private function moveArgumentsToOptions(
        MethodCall $methodCall,
        int $position,
        int $optionsPosition,
        string $className,
        array $argNodes
    ): ?Node {
        $namesToArgs = $this->resolveNamesToArgs($className, $argNodes);

        // set default data in between
        if ($position + 1 !== $optionsPosition && ! isset($methodCall->args[$position + 1])) {
            $methodCall->args[$position + 1] = new Arg($this->createNull());
        }

        // @todo extend current options - array analyzer
        if (! isset($methodCall->args[$optionsPosition])) {
            $optionsArrayNode = new Array_();
            foreach ($namesToArgs as $name => $arg) {
                $optionsArrayNode->items[] = new ArrayItem($arg->value, new String_($name));
            }

            $methodCall->args[$optionsPosition] = new Arg($optionsArrayNode);
        }

        $formTypeClassNode = $this->parsedNodesByType->findClass($className);
        if ($formTypeClassNode === null) {
            return null;
        }

        $formTypeConstructorMethodNode = $formTypeClassNode->getMethod('__construct');

        // nothing we can do, out of scope
        if ($formTypeConstructorMethodNode === null) {
            return null;
        }

        // add "buildForm" method + "configureOptions" method with defaults
        $this->addBuildFormMethod($formTypeClassNode, $formTypeConstructorMethodNode);
        $this->addConfigureOptionsMethod($formTypeClassNode, $namesToArgs);

        // remove ctor
        $this->removeNode($formTypeConstructorMethodNode);

        return $methodCall;
    }

    /**
     * @param Arg[] $argNodes
     * @return Arg[]
     */
    private function resolveNamesToArgs(string $className, array $argNodes): array
    {
        $reflectionClass = new ReflectionClass($className);
        $constructorReflectionMethod = $reflectionClass->getConstructor();

        if ($constructorReflectionMethod === null) {
            return [];
        }

        $namesToArgs = [];
        foreach ($constructorReflectionMethod->getParameters() as $parameterReflection) {
            $namesToArgs[$parameterReflection->getName()] = $argNodes[$parameterReflection->getPosition()];
        }

        return $namesToArgs;
    }

    private function addBuildFormMethod(Class_ $classNode, ClassMethod $classMethod): void
    {
        if ($classNode->getMethod('buildForm') !== null) {
            // @todo
            return;
        }

        $formBuilderParamBuilder = $this->builderFactory->param('builder');
        $formBuilderParamBuilder->setType(new FullyQualified(FormBuilderInterface::class));

        $formBuilderParam = $formBuilderParamBuilder->getNode();

        $optionsParamBuilder = $this->builderFactory->param('options');
        $optionsParamBuilder->setType('array');

        $optionsParam = $optionsParamBuilder->getNode();

        $buildFormClassMethodBuilder = $this->builderFactory->method('buildForm');
        $buildFormClassMethodBuilder->makePublic();
        $buildFormClassMethodBuilder->addParam($formBuilderParam);
        $buildFormClassMethodBuilder->addParam($optionsParam);
        // raw copy stmts from ctor @todo improve
        $buildFormClassMethodBuilder->addStmts(
            $this->replaceParameterAssignWithOptionAssign((array) $classMethod->stmts, $optionsParam)
        );

        $buildFormClassMethodNode = $buildFormClassMethodBuilder->getNode();

        $classNode->stmts[] = $buildFormClassMethodNode;
    }

    /**
     * @param Arg[] $namesToArgs
     */
    private function addConfigureOptionsMethod(Class_ $classNode, array $namesToArgs): void
    {
        if ($classNode->getMethod('configureOptions') !== null) {
            // @todo
            return;
        }

        $resolverParamBuilder = $this->builderFactory->param('resolver');
        $resolverParamBuilder->setType(new FullyQualified(OptionsResolver::class));

        $resolverParam = $resolverParamBuilder->getNode();

        $optionsDefaults = new Array_();

        foreach (array_keys($namesToArgs) as $optionName) {
            $optionsDefaults->items[] = new ArrayItem($this->createNull(), new String_($optionName));
        }

        $setDefaultsMethodCall = new MethodCall($resolverParam->var, new Identifier('setDefaults'), [
            new Arg($optionsDefaults),
        ]);

        $configureOptionsClassMethodBuilder = $this->builderFactory->method('configureOptions');
        $configureOptionsClassMethodBuilder->makePublic();
        $configureOptionsClassMethodBuilder->addParam($resolverParam);
        $configureOptionsClassMethodBuilder->addStmt($setDefaultsMethodCall);

        $configureOptionsClassMethod = $configureOptionsClassMethodBuilder->getNode();

        $classNode->stmts[] = $configureOptionsClassMethod;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     *
     * $this->value = $value
     * ↓
     * $this->value = $options['value']
     */
    private function replaceParameterAssignWithOptionAssign(array $nodes, Param $param): array
    {
        foreach ($nodes as $expression) {
            if (! $expression instanceof Expression) {
                continue;
            }

            $node = $expression->expr;
            if (! $node instanceof Assign) {
                continue;
            }

            $variableName = $this->getName($node->var);
            if ($variableName === null) {
                continue;
            }

            if ($node->expr instanceof Variable) {
                $node->expr = new ArrayDimFetch($param->var, new String_($variableName));
            }
        }

        return $nodes;
    }
}
