<?php

namespace Rector\Php\Tests\Rector\String_\SensitiveHereNowDocRector\Wrong;

function sensitiveHereNowDoc()
{
    $value = <<<CODE_SAMPLE
    CODE_SAMPLE_2
CODE_SAMPLE;

    $value = <<<CODE_SAMPLE
    CODE_SAMPLE_WRAP_2
CODE_SAMPLE;

// examples from RFC
    $value = <<<END
a
b
ENDING
END;
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\String_\SensitiveHereNowDocRector\Wrong;

function sensitiveHereNowDoc()
{
    $value = <<<CODE_SAMPLE_WRAP
    CODE_SAMPLE_2
CODE_SAMPLE_WRAP;

    $value = <<<CODE_SAMPLE_WRAP_1
    CODE_SAMPLE_WRAP_2
CODE_SAMPLE_WRAP_1;

// examples from RFC
    $value = <<<END_WRAP
a
b
ENDING
END_WRAP;
}

?>
