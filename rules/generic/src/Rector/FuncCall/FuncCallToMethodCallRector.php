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
    public const FUNCTION_TO_CLASS_TO_METHOD_CALL = 'function_to_class_to_method_call';

    /**
     * @var array<string, array<string, string>>
     */
    private $functionToClassToMethod = [];

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    public function __construct(PropertyNaming $propertyNaming)
    {
        $this->propertyNaming = $propertyNaming;
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
                    self::FUNCTION_TO_CLASS_TO_METHOD_CALL => [
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
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_) {
            return null;
        }

        foreach ($this->functionToClassToMethod as $function => $classMethod) {
            if (! $this->isName($node->name, $function)) {
                continue;
            }

            [$class, $method] = $classMethod;

            $thisVariable = new Variable('this');
            $propertyName = $this->propertyNaming->fqnToVariableName($class);
            $propertyFetch = new PropertyFetch($thisVariable, $propertyName);

            $serviceObjectType = new FullyQualifiedObjectType($class);

            $propertyName = $this->propertyNaming->fqnToVariableName($serviceObjectType);
            $this->addPropertyToClass($classNode, $serviceObjectType, $propertyName);

            return $this->createMethodCall($propertyFetch, $method, $node->args);
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->functionToClassToMethod = $configuration[self::FUNCTION_TO_CLASS_TO_METHOD_CALL] ?? [];
    }
}
