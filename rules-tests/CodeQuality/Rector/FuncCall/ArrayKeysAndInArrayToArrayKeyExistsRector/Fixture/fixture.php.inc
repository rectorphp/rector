<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector\Fixture;

use Rector\Testing\Contract\RunnableInterface;

class SomeClass implements RunnableInterface
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

namespace Rector\CodeQuality\Tests\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector\Fixture;

use Rector\Testing\Contract\RunnableInterface;

class SomeClass implements RunnableInterface
{
    public function run()
    {
        $packageName = "foo";
        $values = ["foo" => "bar"];

        return array_key_exists($packageName, $values);
    }
}

?>
