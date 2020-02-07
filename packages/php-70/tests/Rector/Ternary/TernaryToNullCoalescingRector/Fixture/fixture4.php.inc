<?php

namespace Rector\Php70\Tests\Rector\Ternary\TernaryToNullCoalescingRector\Fixture;

function ternaryToNullCoalescing4()
{
    $d = $e !== null ? $e : 'e';

    $dd = null !== $ee ? $ee : 'ee';

    $i = $this->${'a'}[0]->$$b[1][2]::$c[3][4][5]->xxx->{" $d"} !== null ? $this->${'a'}[0]->$$b[1][2]::$c[3][4][5]->xxx->{" $d"} : 0;

    $j = $this->${'a'}[0]->$$b[1][2]::$c[3][4][5]->xxx->{" $d"} === null ? false : $this->${'a'}[0]->$$b[1][2]::$c[3][4][5]->xxx->{" $d"};

    $k = $this
        ->${'a'}[0]->$$b[1][2]
        ::$c[3][4][5]->{" $d"} !== null
        ? $this
            ->${'a'}[0]
            ->$$b[1][2]
            ::$c[3][4][5]
            ->{" $d"}
        : true;

    $l = \Whatever\Something::$anything !== null ? \Whatever\Something::$anything : 1;

    $m = $object->anything === null ? 0 : $object->anything;

    $n = ($something === null ? false : $something);

    $o[$something === null ? true : $something] = true;

    $p = doSomething()() === null ? false : doSomething()();
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\Ternary\TernaryToNullCoalescingRector\Fixture;

function ternaryToNullCoalescing4()
{
    $d = $e ?? 'e';

    $dd = $ee ?? 'ee';

    $i = $this->${'a'}[0]->$$b[1][2]::$c[3][4][5]->xxx->{" $d"} ?? 0;

    $j = $this->${'a'}[0]->$$b[1][2]::$c[3][4][5]->xxx->{" $d"} ?? false;

    $k = $this
        ->${'a'}[0]
        ->$$b[1][2]
        ::$c[3][4][5]
        ->{" $d"} ?? true;

    $l = \Whatever\Something::$anything ?? 1;

    $m = $object->anything ?? 0;

    $n = ($something ?? false);

    $o[$something ?? true] = true;

    $p = doSomething()() ?? false;
}

?>
