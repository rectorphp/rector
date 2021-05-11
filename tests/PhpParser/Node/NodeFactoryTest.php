<?php

declare(strict_types=1);

namespace Rector\Core\Tests\PhpParser\Node;

use Iterator;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Testing\PHPUnit\AbstractTestCase;

final class NodeFactoryTest extends AbstractTestCase
{
    private NodeFactory $nodeFactory;

    protected function setUp(): void
    {
        $this->boot();

        $this->nodeFactory = $this->getService(NodeFactory::class);
    }

    /**
     * @param int[]|array<string, int> $inputArray
     * @dataProvider provideDataForArray()
     */
    public function testCreateArray(array $inputArray, Array_ $expectedArrayNode): void
    {
        $arrayNode = $this->nodeFactory->createArray($inputArray);

        $this->assertEquals($expectedArrayNode, $arrayNode);
    }

    /**
     * @return Iterator<int[][]|array<string, int>|Array_[]>
     */
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
