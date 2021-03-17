<?php

namespace Rector\Tests\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector\Fixture;

class Fixture
{
    public function run()
    {
        $query = 'bla';
        parse_str($query);
        $data = get_defined_vars();
    }
}

?>
-----
<?php

namespace Rector\Tests\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector\Fixture;

class Fixture
{
    public function run()
    {
        $query = 'bla';
        parse_str($query, $result);
        $data = $result;
    }
}

?>
