<?php

namespace Rector\CodingStyle\Tests\Rector\String_\ManualJsonStringToJsonEncodeArrayRector\Fixture;

final class ConcatJson
{
    public function run()
    {
        $someJsonAsString = '{"role_name":"admin","numberz":{"id":"' . 5 . '"}}';
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\String_\ManualJsonStringToJsonEncodeArrayRector\Fixture;

final class ConcatJson
{
    public function run()
    {
        $jsonData = ['role_name' => 'admin', 'numberz' => ['id' => 5]];
        $someJsonAsString = \Nette\Utils\Json::encode($jsonData);
    }
}

?>
