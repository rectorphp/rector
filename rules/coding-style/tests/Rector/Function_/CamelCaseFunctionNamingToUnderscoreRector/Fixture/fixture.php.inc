<?php

namespace Rector\CodingStyle\Tests\Rector\Function_\CamelCaseFunctionNamingToUnderscoreRector\Fixture;

function someCamelCaseFunction()
{
}

someCamelCaseFunction();

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Function_\CamelCaseFunctionNamingToUnderscoreRector\Fixture;

function some_camel_case_function()
{
}

some_camel_case_function();

?>
