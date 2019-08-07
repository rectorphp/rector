<?php

namespace Rector\CodingStyle\Tests\Rector\String_\ManualJsonStringToJsonEncodeArrayRector\Fixture;

final class WithImplode
{
    public function run()
    {
        $jsonRequest = '{"categories":["'.implode(
                '","',
                [1, 2, 3]
            ).'"],"order_by":"random"}';
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\String_\ManualJsonStringToJsonEncodeArrayRector\Fixture;

final class WithImplode
{
    public function run()
    {
        $jsonData = ['categories' => [1, 2, 3], 'order_by' => 'random'];
        $jsonRequest = \Nette\Utils\Json::encode($jsonData);
    }
}

?>
