<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachToInArrayRector\Wrong;

class SomeClass
{
    public function foreachWithElseNullable()
    {
        foreach ($items as $item) {
            // some comment
            if ($item == 'something') {
                return true;
            }
        }

        return false;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachToInArrayRector\Wrong;

class SomeClass
{
    public function foreachWithElseNullable()
    {
        // some comment
        return in_array('something', $items);
    }
}

?>
