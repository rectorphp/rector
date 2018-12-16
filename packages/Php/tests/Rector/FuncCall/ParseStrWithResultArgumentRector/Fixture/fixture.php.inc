<?php

namespace Rector\Php\Tests\Rector\FuncCall\ParseStrWithResultArgumentRector\Fixture;

class ParsingClass
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

namespace Rector\Php\Tests\Rector\FuncCall\ParseStrWithResultArgumentRector\Fixture;

class ParsingClass
{
    public function run()
    {
        $query = 'bla';
        parse_str($query, $result);
        $data = $result;
    }
}

?>
