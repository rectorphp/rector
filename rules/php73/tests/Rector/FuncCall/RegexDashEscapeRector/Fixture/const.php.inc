<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RegexDashEscapeRector\Fixture;

class ConstPattern
{
    const COMPAT_PATTERN = '#[-\w()]#';
    const NON_COMPAT_PATTERN = '#[\w-()]#';

    public function run()
    {
        preg_match(self::COMPAT_PATTERN, 'some text');
        preg_match(self::NON_COMPAT_PATTERN, 'some text');
    }
}

?>
-----
<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RegexDashEscapeRector\Fixture;

class ConstPattern
{
    const COMPAT_PATTERN = '#[-\w()]#';
    const NON_COMPAT_PATTERN = '#[\w\-()]#';

    public function run()
    {
        preg_match(self::COMPAT_PATTERN, 'some text');
        preg_match(self::NON_COMPAT_PATTERN, 'some text');
    }
}

?>
