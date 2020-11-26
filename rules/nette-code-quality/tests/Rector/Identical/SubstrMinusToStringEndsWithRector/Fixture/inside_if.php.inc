<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Identical\SubstrMinusToStringEndsWithRector\Fixture;

function insideIf($var)
{
    if (substr($var, -4) !== 'Test') {

    }

    if ('Test' !== substr($var, -4)) {

    }

    if (substr($var, -4) === 'Test') {

    }

    if ('Test' === substr($var, -4)) {

    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Identical\SubstrMinusToStringEndsWithRector\Fixture;

function insideIf($var)
{
    if (!\Nette\Utils\Strings::endsWith($var, 'Test')) {

    }

    if (!\Nette\Utils\Strings::endsWith($var, 'Test')) {

    }

    if (\Nette\Utils\Strings::endsWith($var, 'Test')) {

    }

    if (\Nette\Utils\Strings::endsWith($var, 'Test')) {

    }
}

?>
