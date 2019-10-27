<?php

declare(strict_types=1);

namespace Rector\Rector\MagicDisclosure;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Tests\Rector\MagicDisclosure\ToStringToMethodCallRector\ToStringToMethodCallRectorTest
 */
final class ToStringToMethodCallRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $methodNamesByType = [];

    /**
     * Type to method call()
     *
     * @param string[] $methodNamesByType
     */
    public function __construct(array $methodNamesByType = [])
    {
        $this->methodNamesByType = $methodNamesByType;
    }

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
                    'SomeObject' => 'getPath',
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

    private function processStringNode(String_ $stringNode): ?Node
    {
        foreach ($this->methodNamesByType as $type => $methodName) {
            if (! $this->isObjectType($stringNode, $type)) {
                continue;
            }

            return $this->createMethodCall($stringNode->expr, $methodName);
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
