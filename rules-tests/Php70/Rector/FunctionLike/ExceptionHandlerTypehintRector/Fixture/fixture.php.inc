<?php

namespace Rector\Php70\Tests\Rector\FunctionLike\ExceptionHandlerTypehintRector\Fixture;

function exceptionHandlerTypehint()
{
    function handler(\Exception $exception)
    {

    }

    set_error_handler('handler');
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\FunctionLike\ExceptionHandlerTypehintRector\Fixture;

function exceptionHandlerTypehint()
{
    function handler(\Throwable $exception)
    {

    }

    set_error_handler('handler');
}

?>
