<?php

namespace Rector\Tests\CodingStyle\Rector\Assign\ManualJsonStringToJsonEncodeArrayRector\Fixture;

final class NullItem
{
    public function run()
    {
        $json = '{"a":null}';
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Assign\ManualJsonStringToJsonEncodeArrayRector\Fixture;

final class NullItem
{
    public function run()
    {
        $jsonData = ['a' => null];
        $json = \Nette\Utils\Json::encode($jsonData);
    }
}

?>
