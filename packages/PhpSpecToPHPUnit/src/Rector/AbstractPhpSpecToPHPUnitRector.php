<?php declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector;

use PhpParser\Node;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://gnugat.github.io/2015/09/23/phpunit-with-phpspec.html
 * @see http://www.phpspec.net/en/stable/cookbook/construction.html
 */
abstract class AbstractPhpSpecToPHPUnitRector extends AbstractRector
{
    /**
     * @var string
     */
    private $objectBehaviorClass;

    /**
     * @required
     */
    public function autowireObjectBehaviorClass(string $objectBehaviorClass = 'PhpSpec\ObjectBehavior'): void
    {
        $this->objectBehaviorClass = $objectBehaviorClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate PhpSpec behavior to PHPUnit test', [
            new CodeSample(
                <<<'CODE_SAMPLE'
namespace spec\SomeNamespaceForThisTest;

use PhpSpec\ObjectBehavior;

class OrderSpec extends ObjectBehavior
{
    public function let(OrderFactory $factory, ShippingMethod $shippingMethod)
    {
        $factory->createShippingMethodFor(Argument::any())->shouldBeCalled()->willReturn($shippingMethod);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
namespace spec\SomeNamespaceForThisTest;

class OrderSpec extends ObjectBehavior
{
    /**
     * @var \SomeNamespaceForThisTest\Order
     */
    private $order;
    protected function setUp()
    {
        /** @var OrderFactory|\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(OrderFactory::class);

        /** @var ShippingMethod|\PHPUnit\Framework\MockObject\MockObject $shippingMethod */
        $shippingMethod = $this->createMock(ShippingMethod::class);

        $factory->expects($this->once())->method('createShippingMethodFor')->willReturn($shippingMethod);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    public function isInPhpSpecBehavior(Node $node): bool
    {
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return false;
        }

        return $this->isObjectType($classNode, $this->objectBehaviorClass);
    }
}
