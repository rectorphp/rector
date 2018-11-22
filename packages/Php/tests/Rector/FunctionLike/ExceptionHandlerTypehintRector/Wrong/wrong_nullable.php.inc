<?php declare(strict_types=1);

function handler(?Exception $exception)
{

}

set_error_handler('handler');
