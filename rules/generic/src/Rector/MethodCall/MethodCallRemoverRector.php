<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\PhpParser\Node\Manipulator\MethodCallManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Generic\Tests\Rector\MethodCall\MethodCallRemoverRector\MethodCallRemoverRectorTest
 */
final class MethodCallRemoverRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHOD_CALL_REMOVER_ARGUMENT = '$methodCallRemoverArgument';

    /**
     * @var string[]
     */
    private $methodCallRemoverArgument;

    /**
     * @var MethodCallManipulator
     */
    private $methodCallManipulator;

    public function __construct(MethodCallManipulator $methodCallManipulator)
    {
        $this->methodCallManipulator = $methodCallManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns "$this->something()->anything()" to "$this->anything()"', [
            new ConfiguredCodeSample(
                <<<'PHP'
$someObject = new Car;
$someObject->something()->anything();
PHP
                ,
                <<<'PHP'
$someObject = new Car;
$someObject->anything();
PHP
                ,
                [
                    self::METHOD_CALL_REMOVER_ARGUMENT => [
                        self::METHOD_CALL_REMOVER_ARGUMENT => [
                            'Car' => 'something',
                        ],
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->methodCallRemoverArgument as $className => $methodName) {
            if (! $this->isObjectType($node->var, $className)) {
                continue;
            }

            if (! $this->isName($node->name, $methodName)) {
                continue;
            }

            $rootNodeName = $this->getRootNodeVariableName($node);

            if ($rootNodeName === null) {
                continue;
            }

            return new Variable($rootNodeName);
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->methodCallRemoverArgument = $configuration[self::METHOD_CALL_REMOVER_ARGUMENT] ?? [];
    }

    private function getRootNodeVariableName(MethodCall $node): ?string
    {
        $rootNode = $this->methodCallManipulator->resolveRootVariable($node);
        return $this->getName($rootNode);
    }
}
