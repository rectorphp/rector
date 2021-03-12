<?php

namespace Rector\CodingStyle\Tests\Rector\Assign\ManualJsonStringToJsonEncodeArrayRector\Fixture;

final class MultilineConcatJson
{
    public function run()
    {
        $someJsonAsString = '{"role_name":"admin","numberz":{"id":"';
        $someJsonAsString .= 5 . '"}}';
    }

    public function fun()
    {
        $someJsonAsString = '{"role_name":"admin","numberz":{"id":"5';
        $someJsonAsString .= '"}}';
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Assign\ManualJsonStringToJsonEncodeArrayRector\Fixture;

final class MultilineConcatJson
{
    public function run()
    {
        $jsonData = ['role_name' => 'admin', 'numberz' => ['id' => 5]];
        $someJsonAsString = \Nette\Utils\Json::encode($jsonData);
    }

    public function fun()
    {
        $jsonData = ['role_name' => 'admin', 'numberz' => ['id' => '5']];
        $someJsonAsString = \Nette\Utils\Json::encode($jsonData);
    }
}

?>
