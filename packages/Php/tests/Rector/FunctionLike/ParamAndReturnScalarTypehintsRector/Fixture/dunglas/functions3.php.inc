<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\Dunglas;

function myFunction($param = false)
{
}
/**
 * @param string $param
 */
function myOtherFunction($param)
{
}
?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\Dunglas;

function myFunction($param = false)
{
}
/**
 * @param string $param
 */
function myOtherFunction(string $param)
{
}
?>
