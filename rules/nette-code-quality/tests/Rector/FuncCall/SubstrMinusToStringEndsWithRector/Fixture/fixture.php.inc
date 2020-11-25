<?php

namespace Rector\NetteCodeQuality\Tests\Rector\FuncCall\SubstrMinusToStringEndsWithRector\Fixture;

function check($var)
{
    substr($var, -4) !== 'Test';
    substr($var, -4) === 'Test';
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\FuncCall\SubstrMinusToStringEndsWithRector\Fixture;

function check($var)
{
    !\Nette\Utils\Strings::endsWith($var, 'Test');
    \Nette\Utils\Strings::endsWith($var, 'Test');
}

?>
