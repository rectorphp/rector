<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\BuilderFactory;
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
use Rector\NodeTypeResolver\Application\ClassLikeNodeCollector;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use ReflectionClass;

/**
 * @see https://github.com/symfony/symfony/commit/adf20c86fb0d8dc2859aa0d2821fe339d3551347
 * @see http://www.keganv.com/passing-arguments-controller-file-type-symfony-3/
 * @see https://stackoverflow.com/questions/34027711/passing-data-to-buildform-in-symfony-2-8-3-0
 * @see https://github.com/symfony/symfony/blob/2.8/UPGRADE-2.8.md#form
 */
final class FormTypeInstanceToClassConstRector extends AbstractRector
{
    /**
     * @var string
     */
    private $controllerClass;

    /**
     * @var string
     */
    private $formBuilderType;

    /**
     * @var string
     */
    private $formType;

    /**
     * @var ClassLikeNodeCollector
     */
    private $classLikeNodeCollector;

    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    public function __construct(
        ClassLikeNodeCollector $classLikeNodeCollector,
        BuilderFactory $builderFactory,
        string $controllerClass = 'Symfony\Bundle\FrameworkBundle\Controller\Controller',
        string $formBuilderType = 'Symfony\Component\Form\FormBuilderInterface',
        string $formType = 'Symfony\Component\Form\FormInterface'
    ) {
        $this->classLikeNodeCollector = $classLikeNodeCollector;
        $this->controllerClass = $controllerClass;
        $this->formBuilderType = $formBuilderType;
        $this->formType = $formType;
        $this->builderFactory = $builderFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes createForm(new FormType), add(new FormType) to ones with "FormType::class"',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeController
{
    public function action()
    {
        $form = $this->createForm(TeamType::class, $entity, [
            'action' => $this->generateUrl('teams_update', ['id' => $entity->getId()]),
            'method' => 'PUT',
        ));
    }
}
CODE_SAMPLE
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
        if ($this->isType($node, $this->controllerClass) && $this->isName($node, 'createForm')) {
            return $this->processNewInstance($node, 0, 2);
        }

        if ($this->isTypes($node, [$this->formBuilderType, $this->formType]) && $this->isName($node, 'add')) {
            return $this->processNewInstance($node, 1, 2);
        }

        return null;
    }

    private function processNewInstance(MethodCall $methodCallNode, int $position, int $optionsPosition): ?Node
    {
        if (! isset($methodCallNode->args[$position])) {
            return null;
        }

        if (! $methodCallNode->args[$position]->value instanceof New_) {
            return null;
        }

        /** @var New_ $newNode */
        $newNode = $methodCallNode->args[$position]->value;

        // we can only process direct name
        if (! $newNode->class instanceof Name) {
            return null;
        }

        if (count($newNode->args)) {
            $methodCallNode = $this->moveArgumentsToOptions(
                $methodCallNode,
                $position,
                $optionsPosition,
                $newNode->class->toString(),
                $newNode->args
            );
            if ($methodCallNode === null) {
                return null;
            }
        }

        $methodCallNode->args[$position]->value = new ClassConstFetch($newNode->class, 'class');

        return $methodCallNode;
    }

    /**
     * @param Arg[] $argNodes
     */
    private function moveArgumentsToOptions(
        MethodCall $methodCallNode,
        int $position,
        int $optionsPosition,
        string $className,
        array $argNodes
    ): ?Node {
        $namesToArgs = $this->resolveNamesToArgs($className, $argNodes);

        // set default data in between
        if ($position + 1 !== $optionsPosition) {
            if (! isset($methodCallNode->args[$position + 1])) {
                $methodCallNode->args[$position + 1] = new Arg($this->createNull());
            }
        }

        // @todo extend current options - array analyzer
        if (! isset($methodCallNode->args[$optionsPosition])) {
            $optionsArrayNode = new Array_();
            foreach ($namesToArgs as $name => $arg) {
                $optionsArrayNode->items[] = new ArrayItem($arg->value, new String_($name));
            }

            $methodCallNode->args[$optionsPosition] = new Arg($optionsArrayNode);
        }

        $formTypeClassNode = $this->classLikeNodeCollector->findClass($className);
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

        return $methodCallNode;
    }

    /**
     * @param Arg[] $argNodes
     * @return Arg[]
     */
    private function resolveNamesToArgs(string $className, array $argNodes): array
    {
        $reflectionClass = new ReflectionClass($className);
        $constructorReflectionMethod = $reflectionClass->getConstructor();

        if (! $constructorReflectionMethod) {
            return [];
        }

        $namesToArgs = [];
        foreach ($constructorReflectionMethod->getParameters() as $parameterReflection) {
            $namesToArgs[$parameterReflection->getName()] = $argNodes[$parameterReflection->getPosition()];
        }

        return $namesToArgs;
    }

    private function addBuildFormMethod(Class_ $classNode, ClassMethod $formTypeConstructorMethodNode): void
    {
        if ($classNode->getMethod('buildForm')) {
            // @todo
            return;
        }

        $formBuilderParamNode = $this->builderFactory->param('builder')
            ->setType(new FullyQualified($this->formBuilderType))
            ->getNode();

        $optionsParamNode = $this->builderFactory->param('options')
            ->setType('array')
            ->getNode();

        $buildFormClassMethodNode = $this->builderFactory->method('buildForm')
            ->makePublic()
            ->addParam($formBuilderParamNode)
            ->addParam($optionsParamNode)
            // raw copy stmts from ctor @todo improve
            ->addStmts(
                $this->replaceParameterAssignWithOptionAssign(
                    (array) $formTypeConstructorMethodNode->stmts,
                    $optionsParamNode
                )
            )
            ->getNode();

        $classNode->stmts[] = $buildFormClassMethodNode;
    }

    /**
     * @param Arg[] $namesToArgs
     */
    private function addConfigureOptionsMethod(Class_ $classNode, array $namesToArgs): void
    {
        if ($classNode->getMethod('configureOptions')) {
            // @todo
            return;
        }

        $resolverParamNode = $this->builderFactory->param('resolver')
            ->setType(new FullyQualified('Symfony\Component\OptionsResolver\OptionsResolver'))
            ->getNode();

        $optionsDefaults = new Array_();

        foreach (array_keys($namesToArgs) as $optionName) {
            $optionsDefaults->items[] = new ArrayItem($this->createNull(), new String_($optionName));
        }

        $setDefaultsMethodCall = new MethodCall($resolverParamNode->var, new Identifier('setDefaults'), [
            new Arg($optionsDefaults),
        ]);

        $configureOptionsClassMethodNode = $this->builderFactory->method('configureOptions')
            ->makePublic()
            ->addParam($resolverParamNode)
            ->addStmt($setDefaultsMethodCall)
            ->getNode();

        $classNode->stmts[] = $configureOptionsClassMethodNode;
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
