<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests;

use PhpParser\Node\Expr\Variable;
use Rector\Node\Attribute;

final class LazyTypeAttributeTest extends AbstractNodeTypeResolverTest
{
    /**
     * @var bool
     */
    private $isCalled = false;

    /**
     * @var Variable
     */
    private $variableNode;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Variable[] $nodes */
        $nodes = $this->getNodesForFileOfType(__DIR__ . '/LazyTypeAttributeSource/MethodCall.php.inc', Variable::class);
        $this->assertCount(3, $nodes);

        $this->variableNode = $nodes[2];
    }

    public function testLazyPrototype(): void
    {
        $this->variableNode->setAttribute('someKey', function () {
            $this->isCalled = true;
            return 5;
        });
        $this->assertFalse($this->isCalled);

        $this->assertTrue(is_callable($this->variableNode->getAttribute('someKey')));

        $this->assertSame(5, $this->variableNode->getAttribute('someKey')());
        $this->assertTrue($this->isCalled);
    }

    public function test(): void
    {
        $this->assertTrue($this->variableNode->hasAttribute(Attribute::TYPES));
    }
}
