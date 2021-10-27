<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeXMLParserCreate
{
    public function run()
    {
        $res = xml_parser_create();
        is_resource($res);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeXMLParserCreate
{
    public function run()
    {
        $res = xml_parser_create();
        $res instanceof \XMLParser;
    }
}

?>
