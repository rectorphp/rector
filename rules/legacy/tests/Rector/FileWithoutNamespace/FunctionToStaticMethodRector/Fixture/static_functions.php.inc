<?php

declare(strict_types=1);

namespace Rector\Legacy\Tests\Rector\FileWithoutNamespace\FunctionToStaticMethodRector\Fixture;

function first_static_function()
{
    return 5;
}

$value = first_static_function();

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Legacy\Tests\Rector\FileWithoutNamespace\FunctionToStaticMethodRector\Fixture;

$value = %sStaticFunctions::firstStaticFunction();

?>
