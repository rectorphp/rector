<?php declare(strict_types=1);

namespace Rector\Rector\Argument;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ArgumentRemoverRector extends AbstractRector
{
    /**
     * @var mixed[]
     */
    private $positionsByMethodNameByClassType = [];

    /**
     * @param mixed[] $positionsByMethodNameByClassType
     */
    public function __construct(array $positionsByMethodNameByClassType)
    {
        $this->positionsByMethodNameByClassType = $positionsByMethodNameByClassType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Removes defined arguments in defined methods and their calls.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod(true);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod();'
CODE_SAMPLE
                    ,
                    [
                        'ExampleClass' => [
                            'someMethod' => [
                                0 => [
                                    'value' => 'true',
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
            if (! $this->isType($node, $type)) {
                continue;
            }

            foreach ($positionByMethodName as $methodName => $positions) {
                if (! $this->isName($node, $methodName)) {
                    continue;
                }

                foreach ($positions as $position => $match) {
                    $this->processPosition($node, $position, $match);
                }
            }
        }

        return $node;
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
     * @param mixed[] $values
     */
    private function isArgumentValueMatch(Arg $arg, array $values): bool
    {
        $nodeValue = $this->getValue($arg->value);

        return in_array($nodeValue, $values, true);
    }

    /**
     * @param ClassMethod|StaticCall|MethodCall $node
     */
    private function removeByName(Node $node, int $position, string $name): void
    {
        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            if (isset($node->args[$position])) {
                if ($this->isName($node->args[$position], $name)) {
                    unset($node->args[$position]);
                }

                return;
            }
        }

        if (isset($node->params[$position])) {
            if ($this->isName($node->params[$position], $name)) {
                unset($node->params[$position]);
            }

            return;
        }
    }
}
