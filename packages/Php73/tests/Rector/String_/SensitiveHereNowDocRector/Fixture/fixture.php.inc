<?php

namespace Rector\Php73\Tests\Rector\String_\SensitiveHereNowDocRector\Fixture;

function sensitiveHereNowDoc()
{
    $value = <<<PHP
    PHP_2
PHP;

    $value = <<<PHP
    PHP_WRAP_2
PHP;

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

namespace Rector\Php73\Tests\Rector\String_\SensitiveHereNowDocRector\Fixture;

function sensitiveHereNowDoc()
{
    $value = <<<PHP_WRAP
    PHP_2
PHP_WRAP;

    $value = <<<PHP_WRAP_1
    PHP_WRAP_2
PHP_WRAP_1;

// examples from RFC
    $value = <<<END_WRAP
a
b
ENDING
END_WRAP;
}

?>
