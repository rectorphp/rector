<?php

namespace Rector\Php70\Tests\Rector\FuncCall\RandomFunctionRector\Fixture;

function randomFunction()
{
    rand();

    srand($a);

    $a = &srand($a);

    /* foo */
    srand/** bar */
    ($a);

    a(getrandmax());

    a(rand());

    a(srand());

    rand($d, rand($a, $b));

    rand($a, \Other\Scope\mt_rand($a));

    $a = rand(1, 2) + mt_rand(3, 4);
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\FuncCall\RandomFunctionRector\Fixture;

function randomFunction()
{
    random_int(0, mt_getrandmax());

    mt_srand($a);

    $a = &mt_srand($a);

    /* foo */
    mt_srand/** bar */
    ($a);

    a(mt_getrandmax());

    a(random_int(0, mt_getrandmax()));

    a(mt_srand());

    random_int($d, random_int($a, $b));

    random_int($a, \Other\Scope\mt_rand($a));

    $a = random_int(1, 2) + random_int(3, 4);
}

?>
