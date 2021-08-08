<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

use PHPUnit\Framework\TestCase;

class SkipInDirectArrayValuePHPUnitDataProvider extends TestCase
{
    protected function getData()
    {
        return [
            ['a', 'b', 'c'],
        ];
    }

    public function dataProvider()
    {
        $data = $this->getData();

        return $data;
    }

    /**
     * @dataProvider dataProvider
     */
    public function test($a, $b, $c)
    {
    }
}
