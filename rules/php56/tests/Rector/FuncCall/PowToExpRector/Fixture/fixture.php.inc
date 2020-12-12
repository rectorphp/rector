<?php

namespace Rector\Php56\Tests\Rector\FuncCall\PowToExpRector\Fixture;

function powToExp()
{
    $result = pow(1, 2);

    $result = pow(1.2, 2.3);

    echo pow(-2, 3);

    pow($a--, ++$b);

    \a\pow(5, 6);
    pow(7, 8);

    pow(pow(9, 10), pow(11, 12));

    pow(1 + 2, 3 * 4);

    pow($b = 4, 3);

    \pow(13, 14);

    pow($$a, $$b);
}

?>
-----
<?php

namespace Rector\Php56\Tests\Rector\FuncCall\PowToExpRector\Fixture;

function powToExp()
{
    $result = 1 ** 2;

    $result = 1.2 ** 2.3;

    echo (-2) ** 3;

    ($a--) ** (++$b);

    \a\pow(5, 6);
    7 ** 8;

    (9 ** 10) ** 11 ** 12;

    (1 + 2) ** (3 * 4);

    ($b = 4) ** 3;

    13 ** 14;

    $$a ** $$b;
}

?>
