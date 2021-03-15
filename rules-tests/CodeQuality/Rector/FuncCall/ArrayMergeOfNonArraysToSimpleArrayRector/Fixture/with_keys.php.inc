<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector\Fixture;

class WithKeys
{
    public function go()
    {
        $type = 't';
        $query = 'q';

        $res = array_merge(['results' => []], ['query' => $query, 'type' => $type]);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector\Fixture;

class WithKeys
{
    public function go()
    {
        $type = 't';
        $query = 'q';

        $res = ['results' => [], 'query' => $query, 'type' => $type];
    }
}

?>
