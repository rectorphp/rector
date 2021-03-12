<?php

namespace Rector\Tests\NetteCodeQuality\Rector\Identical\SubstrMinusToStringEndsWithRector\Fixture;

function withVarValue($var, $value)
{
    substr($var, -4) !== $value;
    $value !== substr($var, -4);
    substr($var, -4) === $value;
    $value === substr($var, -4);
}

?>
-----
<?php

namespace Rector\Tests\NetteCodeQuality\Rector\Identical\SubstrMinusToStringEndsWithRector\Fixture;

function withVarValue($var, $value)
{
    !\Nette\Utils\Strings::endsWith($var, $value);
    !\Nette\Utils\Strings::endsWith($var, $value);
    \Nette\Utils\Strings::endsWith($var, $value);
    \Nette\Utils\Strings::endsWith($var, $value);
}

?>
