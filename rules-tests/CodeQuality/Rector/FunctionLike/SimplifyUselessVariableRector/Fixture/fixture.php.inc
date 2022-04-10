<?php

namespace Rector\Tests\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector\Fixture;

function () {
    $a = true;
    return $a;
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

namespace Rector\Tests\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector\Fixture;

function () {
    return true;
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
