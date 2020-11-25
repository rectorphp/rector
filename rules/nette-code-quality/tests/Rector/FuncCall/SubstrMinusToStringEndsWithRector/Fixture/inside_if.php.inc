<?php

namespace Rector\NetteCodeQuality\Tests\Rector\FuncCall\SubstrMinusToStringEndsWithRector\Fixture;

function insideIf($var)
{
    if (substr($var, -4) !== 'Test') {

    }

    if (substr($var, -4) === 'Test') {

    }
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\FuncCall\SubstrMinusToStringEndsWithRector\Fixture;

function insideIf($var)
{
    if (!\Nette\Utils\Strings::endsWith($var, 'Test')) {

    }

    if (\Nette\Utils\Strings::endsWith($var, 'Test')) {

    }
}

?>
