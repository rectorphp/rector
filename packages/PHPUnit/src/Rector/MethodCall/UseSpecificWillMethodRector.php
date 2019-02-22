<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/4160
 * @see https://github.com/symfony/symfony/pull/29685/files
 */
final class UseSpecificWillMethodRector extends AbstractPHPUnitRector
{
    /**
     * @var string[]
     */
    private $nestedMethodToRenameMap = [
        'returnArgument' => 'willReturnArgument',
        'returnCallback' => 'willReturnCallback',
        'returnSelf' => 'willReturnSelf',
        'returnValue' => 'willReturn',
        'returnValueMap' => 'willReturnMap',
        'throwException' => 'willThrowException',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes ->will($this->xxx()) to one specific method', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass extends PHPUnit\Framework\TestCase
{
    public function test()
    {
        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();
        $translator->expects($this->any())
            ->method('trans')
            ->with($this->equalTo('old max {{ max }}!'))
            ->will($this->returnValue('translated max {{ max }}!'));
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass extends PHPUnit\Framework\TestCase
{
    public function test()
    {
        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();
        $translator->expects($this->any())
            ->method('trans')
            ->with('old max {{ max }}!')
            ->willReturnValue('translated max {{ max }}!');
    }
}
CODE_SAMPLE
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
        if (! $this->isInTestClass($node)) {
            return null;
        }

        if (! $this->isType($node, 'PHPUnit\Framework\MockObject\Builder\InvocationMocker')) {
            return null;
        }

        if ($this->isNameInsensitive($node, 'with')) {
            return $this->processWithCall($node);
        }

        if ($this->isNameInsensitive($node, 'will')) {
            return $this->processWillCall($node);
        }

        return null;
    }

    private function processWithCall(MethodCall $methodCall): ?MethodCall
    {
        foreach ($methodCall->args as $i => $argNode) {
            if ($argNode->value instanceof MethodCall && $this->isName($argNode->value, 'equalTo')) {
                $methodCall->args[$i] = $argNode->value->args[0];
            }
        }

        return $methodCall;
    }

    private function processWillCall(MethodCall $methodCall): ?MethodCall
    {
        if (! $methodCall->args[0]->value instanceof MethodCall) {
            return null;
        }

        $nestedMethodCall = $methodCall->args[0]->value;

        foreach ($this->nestedMethodToRenameMap as $oldMethodName => $newParentMethodName) {
            if ($this->isNameInsensitive($nestedMethodCall, $oldMethodName)) {
                $methodCall->name = new Identifier($newParentMethodName);

                // move args up
                $methodCall->args = $nestedMethodCall->args;

                return $methodCall;
            }
        }

        return null;
    }
}
