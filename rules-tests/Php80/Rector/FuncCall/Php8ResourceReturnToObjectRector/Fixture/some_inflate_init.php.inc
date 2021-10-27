<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeInflateInit
{
    public function run()
    {
        $context = inflate_init(ZLIB_ENCODING_DEFLATE);
        is_resource($context);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeInflateInit
{
    public function run()
    {
        $context = inflate_init(ZLIB_ENCODING_DEFLATE);
        $context instanceof \InflateContext;
    }
}

?>
