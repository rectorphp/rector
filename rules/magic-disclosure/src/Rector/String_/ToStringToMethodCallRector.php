<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\MagicDisclosure\Tests\Rector\String_\ToStringToMethodCallRector\ToStringToMethodCallRectorTest
 */
final class ToStringToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const METHOD_NAMES_BY_TYPE = '$methodNamesByType';

    /**
     * @var string[]
     */
    private $methodNamesByType = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined code uses of "__toString()" method  to specific method calls.', [
            new ConfiguredCodeSample(
<<<'PHP'
$someValue = new SomeObject;
$result = (string) $someValue;
$result = $someValue->__toString();
PHP
                ,
<<<'PHP'
$someValue = new SomeObject;
$result = $someValue->getPath();
$result = $someValue->getPath();
PHP
                ,
                [
                    self::METHOD_NAMES_BY_TYPE => [
                        'SomeObject' => 'getPath',
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
        return [String_::class, MethodCall::class];
    }

    /**
     * @param String_|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof String_) {
            return $this->processStringNode($node);
        }

        return $this->processMethodCall($node);
    }

    public function configure(array $configuration): void
    {
        $this->methodNamesByType = $configuration[self::METHOD_NAMES_BY_TYPE] ?? [];
    }

    private function processStringNode(String_ $string): ?Node
    {
        foreach ($this->methodNamesByType as $type => $methodName) {
            if (! $this->isObjectType($string, $type)) {
                continue;
            }

            return $this->createMethodCall($string->expr, $methodName);
        }

        return null;
    }

    private function processMethodCall(MethodCall $methodCall): ?Node
    {
        foreach ($this->methodNamesByType as $type => $methodName) {
            if (! $this->isObjectType($methodCall, $type)) {
                continue;
            }

            if (! $this->isName($methodCall->name, '__toString')) {
                continue;
            }

            $methodCall->name = new Identifier($methodName);

            return $methodCall;
        }

        return null;
    }
}
