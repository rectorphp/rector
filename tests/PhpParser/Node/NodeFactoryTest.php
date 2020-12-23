<?php

declare(strict_types=1);

namespace Rector\Core\Tests\PhpParser\Node;

use Iterator;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\PhpParser\Node\NodeFactory;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class NodeFactoryTest extends AbstractKernelTestCase
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->nodeFactory = $this->getService(NodeFactory::class);
    }

    /**
     * @param mixed[] $inputArray
     * @dataProvider provideDataForArray()
     */
    public function testCreateArray(array $inputArray, Array_ $expectedArrayNode): void
    {
        $arrayNode = $this->nodeFactory->createArray($inputArray);

        $this->assertEquals($expectedArrayNode, $arrayNode);
    }

    public function provideDataForArray(): Iterator
    {
        $array = new Array_();
        $array->items[] = new ArrayItem(new LNumber(1));

        yield [[1], $array];

        $array = new Array_();
        $array->items[] = new ArrayItem(new LNumber(1), new String_('a'));

        yield [[
            'a' => 1,
        ], $array];
    }
}
