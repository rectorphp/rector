<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeXmlWriterOpenMemory
{
    public function run()
    {
        $writer_resource = xmlwriter_open_memory();
        xmlwriter_start_document($writer_resource, '1.0');

        is_resource($writer_resource);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeXmlWriterOpenMemory
{
    public function run()
    {
        $writer_resource = xmlwriter_open_memory();
        xmlwriter_start_document($writer_resource, '1.0');

        $writer_resource instanceof \XMLWriter;
    }
}

?>
