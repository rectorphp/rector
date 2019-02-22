<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class DelegateExceptionArgumentsRector extends AbstractPHPUnitRector
{
    /**
     * @var string[]
     */
    private $oldToNewMethod = [
        'setExpectedException' => 'expectExceptionMessage',
        'setExpectedExceptionRegExp' => 'expectExceptionMessageRegExp',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Takes `setExpectedException()` 2nd and next arguments to own methods in PHPUnit.',
            [
                new CodeSample(
                    '$this->setExpectedException(Exception::class, "Message", "CODE");',
                    <<<'CODE_SAMPLE'
$this->setExpectedException(Exception::class);
$this->expectExceptionMessage("Message");
$this->expectExceptionCode("CODE");
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
        if (! $this->isInTestClass($node)) {
            return null;
        }

        if (! $this->isNames($node, array_keys($this->oldToNewMethod))) {
            return null;
        }

        if (! isset($node->args[1])) {
            return null;
        }

        /** @var Identifier $identifierNode */
        $identifierNode = $node->name;
        $oldMethodName = $identifierNode->name;

        $this->addNewMethodCall($node, $this->oldToNewMethod[$oldMethodName], $node->args[1]);
        unset($node->args[1]);

        // add exception code method call
        if (isset($node->args[2])) {
            $this->addNewMethodCall($node, 'expectExceptionCode', $node->args[2]);
            unset($node->args[2]);
        }

        return $node;
    }

    private function addNewMethodCall(MethodCall $methodCall, string $methodName, Arg $arg): void
    {
        $expectExceptionMessageMethodCall = $this->createMethodCall('this', $methodName, [$arg]);

        $this->addNodeAfterNode($expectExceptionMessageMethodCall, $methodCall);
    }
}
