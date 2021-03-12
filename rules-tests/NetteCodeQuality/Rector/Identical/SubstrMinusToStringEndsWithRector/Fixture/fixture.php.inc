<?php

namespace Rector\Tests\NetteCodeQuality\Rector\Identical\SubstrMinusToStringEndsWithRector\Fixture;

function check($var)
{
    substr($var, -4) !== 'Test';
    'Test' !== substr($var, -4);
    substr($var, -4) === 'Test';
    'Test' === substr($var, -4);
}

?>
-----
<?php

namespace Rector\Tests\NetteCodeQuality\Rector\Identical\SubstrMinusToStringEndsWithRector\Fixture;

function check($var)
{
    !\Nette\Utils\Strings::endsWith($var, 'Test');
    !\Nette\Utils\Strings::endsWith($var, 'Test');
    \Nette\Utils\Strings::endsWith($var, 'Test');
    \Nette\Utils\Strings::endsWith($var, 'Test');
}

?>
