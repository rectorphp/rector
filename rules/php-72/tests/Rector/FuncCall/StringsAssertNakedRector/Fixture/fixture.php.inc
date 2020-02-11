<?php

namespace Rector\Php72\Tests\Rector\FuncCall\StringsAssertNakedRector\Fixture;

function nakedAssert()
{
    assert('true === true');
    assert("true === true");

    $value = 5;
    assert($value === 5);

    $value = 'string';
    assert($value === 5);

    $value = 'true === true';
    assert($value);

    assert('is_string($returnURL)');
}

?>
-----
<?php

namespace Rector\Php72\Tests\Rector\FuncCall\StringsAssertNakedRector\Fixture;

function nakedAssert()
{
    assert(true === true);
    assert(true === true);

    $value = 5;
    assert($value === 5);

    $value = 'string';
    assert($value === 5);

    $value = 'true === true';
    assert($value);

    assert(is_string($returnURL));
}

?>
