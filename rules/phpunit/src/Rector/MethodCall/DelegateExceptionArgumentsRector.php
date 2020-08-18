<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\DelegateExceptionArgumentsRector\DelegateExceptionArgumentsRectorTest
 */
final class DelegateExceptionArgumentsRector extends AbstractPHPUnitRector
{
    /**
     * @var string[]
     */
    private const OLD_TO_NEW_METHOD = [
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
                    <<<'PHP'
$this->setExpectedException(Exception::class);
$this->expectExceptionMessage("Message");
$this->expectExceptionCode("CODE");
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $oldMethodNames = array_keys(self::OLD_TO_NEW_METHOD);
        if (! $this->isPHPUnitMethodNames($node, $oldMethodNames)) {
            return null;
        }

        if (isset($node->args[1])) {
            /** @var Identifier $identifierNode */
            $identifierNode = $node->name;
            $oldMethodName = $identifierNode->name;

            $call = $this->createPHPUnitCallWithName($node, self::OLD_TO_NEW_METHOD[$oldMethodName]);
            $call->args[] = $node->args[1];
            $this->addNodeAfterNode($call, $node);

            unset($node->args[1]);

            // add exception code method call
            if (isset($node->args[2])) {
                $call = $this->createPHPUnitCallWithName($node, 'expectExceptionCode');
                $call->args[] = $node->args[2];
                $this->addNodeAfterNode($call, $node);

                unset($node->args[2]);
            }
        }

        $node->name = new Identifier('expectException');

        return $node;
    }
}
