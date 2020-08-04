<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Generic\NodeTypeAnalyzer\TypeProvidingExprFromClassResolver;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

/**
 * @see \Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\FuncCallToMethodCallRectorTest
 */
final class FuncCallToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const FUNC_CALL_TO_CLASS_METHOD_CALL = 'function_to_class_to_method_call';

    /**
     * @var array<string, array<string, string>>
     */
    private $functionToClassToMethod = [];

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var TypeProvidingExprFromClassResolver
     */
    private $typeProvidingExprFromClassResolver;

    public function __construct(
        PropertyNaming $propertyNaming,
        TypeProvidingExprFromClassResolver $typeProvidingExprFromClassResolver
    ) {
        $this->propertyNaming = $propertyNaming;
        $this->typeProvidingExprFromClassResolver = $typeProvidingExprFromClassResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined function calls to local method calls.', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        view('...');
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var \Namespaced\SomeRenderer
     */
    private $someRenderer;

    public function __construct(\Namespaced\SomeRenderer $someRenderer)
    {
        $this->someRenderer = $someRenderer;
    }

    public function run()
    {
        $this->someRenderer->view('...');
    }
}
CODE_SAMPLE
                ,
                [
                    self::FUNC_CALL_TO_CLASS_METHOD_CALL => [
                        'view' => ['Namespaced\SomeRenderer', 'render'],
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return null;
        }

        foreach ($this->functionToClassToMethod as $function => $classMethod) {
            if (! $this->isName($node->name, $function)) {
                continue;
            }

            /** @var string $type */
            /** @var string $method */
            [$type, $method] = $classMethod;

            $expr = $this->typeProvidingExprFromClassResolver->resolveTypeProvidingExprFromClass($classLike, $type);
            if ($expr === null) {
                $this->addPropertyTypeToClass($type, $classLike);
                $expr = $this->createPropertyFetchFromClass($type);
            }

            return $this->createMethodCall($expr, $method, $node->args);
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->functionToClassToMethod = $configuration[self::FUNC_CALL_TO_CLASS_METHOD_CALL] ?? [];
    }

    private function addPropertyTypeToClass(string $type, Class_ $class): void
    {
        $serviceObjectType = new FullyQualifiedObjectType($type);
        $propertyName = $this->propertyNaming->fqnToVariableName($serviceObjectType);
        $this->addPropertyToClass($class, $serviceObjectType, $propertyName);
    }

    private function createPropertyFetchFromClass(string $class): PropertyFetch
    {
        $thisVariable = new Variable('this');
        $propertyName = $this->propertyNaming->fqnToVariableName($class);

        return new PropertyFetch($thisVariable, $propertyName);
    }
}
