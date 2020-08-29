<?php

namespace Rector\Php73\Tests\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function skipConditionalErrorCheckConvertingWhenMultipleConditions()
{
    $result = json_decode($json, true, 215);
    if (json_last_error() !== JSON_ERROR_NONE || empty($result)) {
        error_log('Failed to decode JSON');
    }
}

?>
-----
<?php

namespace Rector\Php73\Tests\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function skipConditionalErrorCheckConvertingWhenMultipleConditions()
{
    try {
        $result = json_decode($json, true, 215, JSON_THROW_ON_ERROR);
        if (empty($result)) {
            error_log('Failed to decode JSON');
        }
    } catch (\JsonException $exception) {
        error_log('Failed to decode JSON');
    }
}

?>
