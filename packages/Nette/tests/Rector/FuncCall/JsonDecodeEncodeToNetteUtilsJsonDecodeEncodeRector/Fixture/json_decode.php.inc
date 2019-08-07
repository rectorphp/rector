<?php

namespace Rector\Nette\Tests\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector\Fixture;

class JsonDecode
{
    public function decodeJson(string $jsonString)
    {
        $stdClass = json_decode($jsonString);

        $array = json_decode($jsonString, true);
        $array = json_decode($jsonString, false);
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector\Fixture;

class JsonDecode
{
    public function decodeJson(string $jsonString)
    {
        $stdClass = \Nette\Utils\Json::decode($jsonString);

        $array = \Nette\Utils\Json::decode($jsonString, \Nette\Utils\Json::FORCE_ARRAY);
        $array = \Nette\Utils\Json::decode($jsonString);
    }
}

?>
