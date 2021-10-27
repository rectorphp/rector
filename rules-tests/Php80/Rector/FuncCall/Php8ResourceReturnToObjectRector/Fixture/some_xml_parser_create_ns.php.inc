<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeXMLParserCreateNs
{
    public function run()
    {
        $res = xml_parser_create_ns();
        is_resource($res);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeXMLParserCreateNs
{
    public function run()
    {
        $res = xml_parser_create_ns();
        $res instanceof \XMLParser;
    }
}

?>
