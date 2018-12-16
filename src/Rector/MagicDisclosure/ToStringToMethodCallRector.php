<?php declare(strict_types=1);

namespace Rector\Rector\MagicDisclosure;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

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
    public function __construct(array $methodNamesByType)
    {
        $this->methodNamesByType = $methodNamesByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined code uses of "__toString()" method  to specific method calls.', [
            new ConfiguredCodeSample(
<<<'CODE_SAMPLE'
$someValue = new SomeObject;
$result = (string) $someValue;
$result = $someValue->__toString();
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
$someValue = new SomeObject;
$result = $someValue->getPath();
$result = $someValue->getPath();
CODE_SAMPLE
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
            if (! $this->isType($stringNode, $type)) {
                continue;
            }

            return $this->createMethodCall($stringNode->expr, $methodName);
        }

        return null;
    }

    private function processMethodCall(MethodCall $methodCallNode): ?Node
    {
        foreach ($this->methodNamesByType as $type => $methodName) {
            if (! $this->isType($methodCallNode, $type)) {
                continue;
            }

            if (! $this->isName($methodCallNode, '__toString')) {
                continue;
            }

            $methodCallNode->name = new Identifier($methodName);

            return $methodCallNode;
        }

        return null;
    }
}
