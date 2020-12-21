<?php

namespace Rector\CodingStyle\Tests\Rector\Assign\ManualJsonStringToJsonEncodeArrayRector\Fixture;

final class Fixture
{
    public function run()
    {
        $someJsonAsString = '{"role_name":"admin","numberz":{"id":"10"}}';
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Assign\ManualJsonStringToJsonEncodeArrayRector\Fixture;

final class Fixture
{
    public function run()
    {
        $jsonData = ['role_name' => 'admin', 'numberz' => ['id' => '10']];
        $someJsonAsString = \Nette\Utils\Json::encode($jsonData);
    }
}

?>
