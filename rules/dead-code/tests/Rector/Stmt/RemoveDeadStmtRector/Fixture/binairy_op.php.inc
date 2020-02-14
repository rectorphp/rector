<?php

$a = 1 + 1;

$a++;
++$a;

$a--;
--$a;

1 + 1;
1 * 1;
1 / 1;
1 - 1;
1 % 1;
1 . 1;
1 ** 1;
1 < 2;
1 > 2;
1 >= 2;
1 <= 2;
1 <=> 2;
1 & 2;
1 | 2;
1 << 2;
1 >> 2;
1 !== 2;
1 != 2;
1 == 2;
1 === 2;
1 xor 2;

1 and 2;
1 or 2;
1 ?? 2;
1 && 2;
1 || 2;

function wrapToPreventPhpStanCallingMethods ()
{
    1 + foo();
    foo() + 1;
    foo2() + foo3();
}
?>
-----
<?php

$a = 1 + 1;

$a++;
++$a;

$a--;
--$a;

1 and 2;
1 or 2;
1 ?? 2;
1 && 2;
1 || 2;

function wrapToPreventPhpStanCallingMethods ()
{
    foo();
    foo();
    foo2();
    foo3();
}
?>
