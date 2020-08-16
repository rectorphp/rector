<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Generic\Tests\Rector\ClassMethod\ArgumentRemoverRector\ArgumentRemoverRectorTest
 */
final class ArgumentRemoverRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const POSITIONS_BY_METHOD_NAME_BY_CLASS_TYPE = '$positionsByMethodNameByClassType';

    /**
     * @var mixed[]
     */
    private $positionsByMethodNameByClassType = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Removes defined arguments in defined methods and their calls.',
            [
                new ConfiguredCodeSample(
                    <<<'PHP'
$someObject = new SomeClass;
$someObject->someMethod(true);
PHP
                    ,
                    <<<'PHP'
$someObject = new SomeClass;
$someObject->someMethod();'
PHP
                    ,
                    [
                        self::POSITIONS_BY_METHOD_NAME_BY_CLASS_TYPE => [
                            'ExampleClass' => [
                                'someMethod' => [
                                    0 => [
                                        'value' => 'true',
                                    ],
                                ],
                            ],
                        ],
                    ]
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class, ClassMethod::class];
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->positionsByMethodNameByClassType as $type => $positionByMethodName) {
            if (! $this->isMethodStaticCallOrClassMethodObjectType($node, $type)) {
                continue;
            }

            foreach ($positionByMethodName as $methodName => $positions) {
                if (! $this->isName($node->name, $methodName)) {
                    continue;
                }

                foreach ($positions as $position => $match) {
                    $this->processPosition($node, $position, $match);
                }
            }
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->positionsByMethodNameByClassType = $configuration[self::POSITIONS_BY_METHOD_NAME_BY_CLASS_TYPE] ?? [];
    }

    /**
     * @param ClassMethod|StaticCall|MethodCall $node
     * @param mixed[]|null $match
     */
    private function processPosition(Node $node, int $position, ?array $match): void
    {
        if ($match === null) {
            if ($node instanceof MethodCall || $node instanceof StaticCall) {
                unset($node->args[$position]);
            } else {
                unset($node->params[$position]);
            }
        }

        if ($match) {
            if (isset($match['name'])) {
                $this->removeByName($node, $position, $match['name']);
                return;
            }

            // only argument specific value can be removed
            if ($node instanceof ClassMethod || ! isset($node->args[$position])) {
                return;
            }

            if ($this->isArgumentValueMatch($node->args[$position], $match)) {
                unset($node->args[$position]);
            }
        }
    }

    /**
     * @param ClassMethod|StaticCall|MethodCall $node
     */
    private function removeByName(Node $node, int $position, string $name): void
    {
        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            if (isset($node->args[$position]) && $this->isName($node->args[$position], $name)) {
                $this->removeArg($node, $position);
            }

            return;
        }

        if ($node instanceof ClassMethod) {
            if (isset($node->params[$position]) && $this->isName($node->params[$position], $name)) {
                $this->removeParam($node, $position);
            }

            return;
        }
    }

    /**
     * @param mixed[] $values
     */
    private function isArgumentValueMatch(Arg $arg, array $values): bool
    {
        $nodeValue = $this->getValue($arg->value);

        return in_array($nodeValue, $values, true);
    }
}
