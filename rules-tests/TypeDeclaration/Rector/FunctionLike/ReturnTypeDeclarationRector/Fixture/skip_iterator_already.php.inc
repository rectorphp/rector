<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use Iterator;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;

final class SkipIteratorAlready
{
    /**
     * @return Iterator<int[][]|Array_[]<string, int>[]|Array_[]>
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
