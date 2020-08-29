<?php

namespace Rector\Php73\Tests\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function convertConditionalErrorCheckToCatch()
{
    json_decode($json, true, 215);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Failed to decode JSON');
    }
}

?>
-----
<?php

namespace Rector\Php73\Tests\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function convertConditionalErrorCheckToCatch()
{
    try {
        json_decode($json, true, 215, JSON_THROW_ON_ERROR);
    } catch (\JsonException $exception) {
        error_log('Failed to decode JSON');
    }
}

?>
