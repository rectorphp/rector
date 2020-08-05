<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Generic\Rector\AbstractToMethodCallRector;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\FuncCallToMethodCallRectorTest
 */
final class FuncCallToMethodCallRector extends AbstractToMethodCallRector
{
    /**
     * @var string
     */
    public const FUNC_CALL_TO_CLASS_METHOD_CALL = 'function_to_class_to_method_call';

    /**
     * @var array<string, array<string, string>>
     */
    private $functionToClassToMethod = [];

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

        $classMethod = $node->getAttribute(AttributeKey::METHOD_NODE);
        if (! $classMethod instanceof ClassMethod) {
            return null;
        }

        if ($classMethod->isStatic()) {
            return null;
        }

        foreach ($this->functionToClassToMethod as $function => $classMethodName) {
            if (! $this->isName($node->name, $function)) {
                continue;
            }

            /** @var string $type */
            /** @var string $method */
            [$type, $method] = $classMethodName;

            $expr = $this->matchTypeProvidingExpr($classLike, $classMethod, $type);
            return $this->createMethodCall($expr, $method, $node->args);
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->functionToClassToMethod = $configuration[self::FUNC_CALL_TO_CLASS_METHOD_CALL] ?? [];
    }
}
