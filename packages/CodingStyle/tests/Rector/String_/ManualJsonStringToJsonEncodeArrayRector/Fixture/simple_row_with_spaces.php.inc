<?php

namespace Rector\CodingStyle\Tests\Rector\String_\ManualJsonStringToJsonEncodeArrayRector\Fixture;

final class SimpleRowWithSpaces
{
    public function run()
    {
        $json = '{"name":"Paulie",
            "surname":"Garand"}';
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\String_\ManualJsonStringToJsonEncodeArrayRector\Fixture;

final class SimpleRowWithSpaces
{
    public function run()
    {
        $jsonData = ['name' => 'Paulie', 'surname' => 'Garand'];
        $json = \Nette\Utils\Json::encode($jsonData);
    }
}

?>
