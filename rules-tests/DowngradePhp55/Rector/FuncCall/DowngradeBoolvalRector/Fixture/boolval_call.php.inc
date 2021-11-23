<?php

namespace Rector\Tests\DowngradePhp55\Rector\FuncCall\DowngradeBoolvalRector\Fixture;

/**
 * @param mixed $value
 * @return bool
 */
function toBool($value)
{
    return boolval($value);
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp55\Rector\FuncCall\DowngradeBoolvalRector\Fixture;

/**
 * @param mixed $value
 * @return bool
 */
function toBool($value)
{
    return (bool) $value;
}

?>
