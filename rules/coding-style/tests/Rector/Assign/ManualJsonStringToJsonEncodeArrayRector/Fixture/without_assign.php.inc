<?php

namespace Rector\CodingStyle\Tests\Rector\Assign\ManualJsonStringToJsonEncodeArrayRector\Fixture;

final class WithoutAssign
{
    public function run()
    {
        $jsonRequest = '{"service_provider_profile":{"id":"' . 1 .'"},';
        $jsonRequest .= '"users":[{"id":"'. 2 .'"},{"id":"'. 3 .'"}]}';
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Assign\ManualJsonStringToJsonEncodeArrayRector\Fixture;

final class WithoutAssign
{
    public function run()
    {
        $jsonData = ['service_provider_profile' => ['id' => 1], 'users' => [['id' => 2], ['id' => 3]]];
        $jsonRequest = \Nette\Utils\Json::encode($jsonData);
    }
}

?>
