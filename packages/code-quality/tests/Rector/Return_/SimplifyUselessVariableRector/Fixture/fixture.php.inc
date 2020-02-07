<?php

namespace Rector\CodeQuality\Tests\Rector\Return_\SimplifyUselessVariableRector\Fixture;

function () {
    $a = true;
    return $a;
};

function () {
    $b += 1;
    return $b;
};

function () {
    $c -= 1;
    return $c;
};

function () {
    $d *= 1;
    return $d;
};

function () {
    $e /= 1;
    return $e;
};

function () {
    $f **= 1;
    return $f;
};

function () {
    $g %= 1;
    return $g;
};

function () {
    $h &= 1;
    return $h;
};

function () {
    $i |= 1;
    return $i;
};

function () {
    $j ^= 1;
    return $j;
};

function () {
    $k <<= 1;
    return $k;
};

function () {
    $l >>= 1;
    return $l;
};

function () {
    $m .= 1;
    return $m;
};

function sameVariableInDifferentScope()
{
    $n = array_map(function () {
        return $n + 1;
    }, []);

    return $n;
}

function moreVariableOneWithoutAssigment() {
    $o++;
    $o = 10;

    return $o;
}

function assigmentAsFunctionParametr() {
    doSomething($p = 0);
    return $p;
}

function assigmentAfterAssignment() {
    doSomething($qq = $q = 0);
    return $q;
}

function () {
    $a = 1;
    $b = 1;
    $c = [
        $b-- => $a++,
        --$b => ++$a,
    ];
    return $c;
};

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Return_\SimplifyUselessVariableRector\Fixture;

function () {
    return true;
};

function () {
    return $b + 1;
};

function () {
    return $c - 1;
};

function () {
    return $d * 1;
};

function () {
    return $e / 1;
};

function () {
    return $f ** 1;
};

function () {
    return $g % 1;
};

function () {
    return $h & 1;
};

function () {
    return $i | 1;
};

function () {
    return $j ^ 1;
};

function () {
    return $k << 1;
};

function () {
    return $l >> 1;
};

function () {
    return $m . 1;
};

function sameVariableInDifferentScope()
{
    return array_map(function () {
        return $n + 1;
    }, []);
}

function moreVariableOneWithoutAssigment() {
    $o++;

    return 10;
}

function assigmentAsFunctionParametr() {
    doSomething($p = 0);
    return $p;
}

function assigmentAfterAssignment() {
    doSomething($qq = $q = 0);
    return $q;
}

function () {
    $a = 1;
    $b = 1;
    return [
        $b-- => $a++,
        --$b => ++$a,
    ];
};

?>
