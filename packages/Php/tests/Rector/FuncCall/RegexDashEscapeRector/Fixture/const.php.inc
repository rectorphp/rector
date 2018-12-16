<?php

namespace Rector\Php\Tests\Rector\FuncCall\RegexDashEscapeRector\Fixture;

use Nette\Utils\Strings;

class ConstPattern
{
    const COMPAT_PATTERN = '#[-\w()]#';
    const NON_COMPAT_PATTERN = '#[\w-()]#';
    const EXTERNAL_NON_COMPAT_PATTERN = '#[\w-()]#';

    public function run()
    {
        preg_match(self::COMPAT_PATTERN, 'some text');
        preg_match(self::NON_COMPAT_PATTERN, 'some text');
    }
}

class AnotherClass
{
    public function run()
    {
        Strings::match('...', ConstPattern::EXTERNAL_NON_COMPAT_PATTERN);
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\RegexDashEscapeRector\Fixture;

use Nette\Utils\Strings;

class ConstPattern
{
    const COMPAT_PATTERN = '#[-\w()]#';
    const NON_COMPAT_PATTERN = '#[\w\-()]#';
    const EXTERNAL_NON_COMPAT_PATTERN = '#[\w-()]#';

    public function run()
    {
        preg_match(self::COMPAT_PATTERN, 'some text');
        preg_match(self::NON_COMPAT_PATTERN, 'some text');
    }
}

class AnotherClass
{
    public function run()
    {
        Strings::match('...', ConstPattern::EXTERNAL_NON_COMPAT_PATTERN);
    }
}

?>
