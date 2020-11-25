<?php

namespace Rector\NetteCodeQuality\Tests\Rector\FuncCall\SubstrMinusToStringEndsWithRector\Fixture;

function check($var)
{
    substr($var, -4) !== 'Test';
}

?>
-----
<?php

namespace Rector\NetteCodeQuality\Tests\Rector\FuncCall\SubstrMinusToStringEndsWithRector\Fixture;

function check($var)
{
    !\Nette\Utils\Strings::endsWith($var, 'Test');
}

?>
