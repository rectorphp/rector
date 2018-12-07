<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\Dunglas;

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

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\Dunglas;

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
