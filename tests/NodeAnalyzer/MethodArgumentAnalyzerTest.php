<?php declare(strict_types=1);

namespace Rector\Tests\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BitwiseNot;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\NodeAnalyzer\MethodArgumentAnalyzer;
use Rector\Tests\AbstractContainerAwareTestCase;

final class MethodArgumentAnalyzerTest extends AbstractContainerAwareTestCase
{
    /**
     * @var MethodCall
     */
    private $methodCallNode;

    /**
     * @var MethodArgumentAnalyzer
     */
    private $methodArgumentAnalyzer;

    protected function setUp(): void
    {
        $this->methodArgumentAnalyzer = $this->container->get(MethodArgumentAnalyzer::class);

        $varNode = new Variable('this');
        $this->methodCallNode = new MethodCall($varNode, 'method');
    }

    public function testHasMethodFirstArgument(): void
    {
        $this->assertFalse($this->methodArgumentAnalyzer->hasMethodNthArgument($this->methodCallNode, 1));

        $this->methodCallNode->args[] = new Arg(new String_('argument'));
        $this->assertTrue($this->methodArgumentAnalyzer->hasMethodNthArgument($this->methodCallNode, 1));
    }

    public function testHasMethodSecondArgument(): void
    {
        $this->methodCallNode->args[] = new Arg(new String_('argument'));
        $this->assertFalse($this->methodArgumentAnalyzer->hasMethodNthArgument($this->methodCallNode, 2));

        $this->methodCallNode->args[] = new Arg(new String_('argument'));
        $this->assertTrue($this->methodArgumentAnalyzer->hasMethodNthArgument($this->methodCallNode, 2));
    }

    public function testIsFirstArgumentString(): void
    {
        $this->assertFalse($this->methodArgumentAnalyzer->isMethodNthArgumentString($this->methodCallNode, 1));

        $this->methodCallNode->args[0] = new Arg(new String_('argument'));
        $this->assertTrue($this->methodArgumentAnalyzer->isMethodNthArgumentString($this->methodCallNode, 1));

        $this->methodCallNode->args[0] = new Arg(new BitwiseNot(new String_('argument')));
        $this->assertFalse($this->methodArgumentAnalyzer->isMethodNthArgumentString($this->methodCallNode, 1));
    }

    public function testOnNonMethodCall(): void
    {
        $this->assertFalse($this->methodArgumentAnalyzer->hasMethodNthArgument(new String_('string'), 1));
        $this->assertFalse($this->methodArgumentAnalyzer->hasMethodNthArgument(new String_('string'), 2));
    }
}
