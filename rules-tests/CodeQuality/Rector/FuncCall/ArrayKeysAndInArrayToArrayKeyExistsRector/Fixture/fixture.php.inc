<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector\Fixture;

class SomeClass
{
    public function run()
    {
        $packageName = "foo";
        $values = ["foo" => "bar"];

        $keys = array_keys($values);

        return in_array($packageName, $keys, true);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector\Fixture;

class SomeClass
{
    public function run()
    {
        $packageName = "foo";
        $values = ["foo" => "bar"];

        return array_key_exists($packageName, $values);
    }
}

?>
