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
        $this->assertFalse($this->methodArgumentAnalyzer->hasMethodFirstArgument($this->methodCallNode));

        $this->methodCallNode->args[] = new Arg(new String_('argument'));
        $this->assertTrue($this->methodArgumentAnalyzer->hasMethodFirstArgument($this->methodCallNode));
    }

    public function testHasMethodSecondArgument(): void
    {
        $this->methodCallNode->args[] = new Arg(new String_('argument'));
        $this->assertFalse($this->methodArgumentAnalyzer->hasMethodSecondArgument($this->methodCallNode));

        $this->methodCallNode->args[] = new Arg(new String_('argument'));
        $this->assertTrue($this->methodArgumentAnalyzer->hasMethodSecondArgument($this->methodCallNode));
    }

    public function testIsFirstArgumentString(): void
    {
        $this->assertFalse($this->methodArgumentAnalyzer->isMethodFirstArgumentString($this->methodCallNode));

        $this->methodCallNode->args[0] = new Arg(new String_('argument'));
        $this->assertTrue($this->methodArgumentAnalyzer->isMethodFirstArgumentString($this->methodCallNode));

        $this->methodCallNode->args[0] = new Arg(new BitwiseNot(new String_('argument')));
        $this->assertFalse($this->methodArgumentAnalyzer->isMethodFirstArgumentString($this->methodCallNode));
    }

    public function testOnNonMethodCall(): void
    {
        $this->assertFalse($this->methodArgumentAnalyzer->hasMethodFirstArgument(new String_('string')));
        $this->assertFalse($this->methodArgumentAnalyzer->hasMethodSecondArgument(new String_('string')));
    }
}
