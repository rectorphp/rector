<?php

namespace Rector\Tests\Php73\Rector\FuncCall\RegexDashEscapeRector\Fixture;

use Nette\Utils\Strings;

class ExternalConstPattern
{
    const EXTERNAL_NON_COMPAT_PATTERN = '#[\w-()]#';
}

class AnotherExternalClass
{
    public function run()
    {
        Strings::match('...', ExternalConstPattern::EXTERNAL_NON_COMPAT_PATTERN);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php73\Rector\FuncCall\RegexDashEscapeRector\Fixture;

use Nette\Utils\Strings;

class ExternalConstPattern
{
    const EXTERNAL_NON_COMPAT_PATTERN = '#[\w\-()]#';
}

class AnotherExternalClass
{
    public function run()
    {
        Strings::match('...', ExternalConstPattern::EXTERNAL_NON_COMPAT_PATTERN);
    }
}

?>
