<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\ArrayKeysAndInArrayToIssetRector\Fixture;

class SomeClass
{
    public function run($packageName, $values)
    {
        $keys = array_keys($values);
        return in_array($packageName, $keys, true);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\ArrayKeysAndInArrayToIssetRector\Fixture;

class SomeClass
{
    public function run($packageName, $values)
    {
        return array_key_exists($values, $packageName);
    }
}

?>
