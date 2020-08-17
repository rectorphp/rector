<?php

namespace Rector\CodingStyle\Tests\Rector\Assign\ManualJsonStringToJsonEncodeArrayRector\Fixture;

final class ArrayConcat
{
    public function run()
    {
        $json = '{"due_date_days":14,"billing_address":' . '{"name":"A","address":"B","city":"C",';
        $json .= '"zip":"180 00"},"administrators":' . 5;
        $json .= ',"settings":['. 3 .','. 4 .']}';
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Assign\ManualJsonStringToJsonEncodeArrayRector\Fixture;

final class ArrayConcat
{
    public function run()
    {
        $jsonData = ['due_date_days' => 14, 'billing_address' => ['name' => 'A', 'address' => 'B', 'city' => 'C', 'zip' => '180 00'], 'administrators' => 5, 'settings' => [3, 4]];
        $json = \Nette\Utils\Json::encode($jsonData);
    }
}

?>
