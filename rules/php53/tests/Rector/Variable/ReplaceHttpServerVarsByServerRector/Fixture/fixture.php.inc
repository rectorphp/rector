<?php

namespace Rector\Php53\Tests\Rector\Variable\ReplaceHttpServerVarsByServerRector\Fixture;

class Fixture
{
    public function run()
    {
        $tmp = $HTTP_SERVER_VARS[$var];
        parse_str($HTTP_SERVER_VARS["QUERY_STRING"]);
    }
}

?>
-----
<?php

namespace Rector\Php53\Tests\Rector\Variable\ReplaceHttpServerVarsByServerRector\Fixture;

class Fixture
{
    public function run()
    {
        $tmp = $_SERVER[$var];
        parse_str($_SERVER["QUERY_STRING"]);
    }
}

?>
