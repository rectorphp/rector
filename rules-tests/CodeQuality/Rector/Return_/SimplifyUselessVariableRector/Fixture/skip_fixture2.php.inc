<?php

namespace Rector\CodeQuality\Tests\Rector\Return_\SimplifyUselessVariableRector\Fixture;

class SkipFixture2
{
    private $noError = 'noError';

    private static $static;

    public function __construct($parameter)
    {
        self::$static = 'static';
        $this->$parameter = 'this';
    }

}

$noErrorToo = null;
function ($noError = 'noError') use ($noErrorToo) {

};

$used = true;

function foo($foo) {
    return preg_replace_callback('~~', function ($matches) {
        return $matches[0];
    }, $foo);
}

return $used;

function ($values) {
    $foo = '';

    foreach ($values as $value) {
        echo $foo . $value;
    }
};

function () {
    for ($i = 0; $i < 10; $i++) {
    }
};

function ($values) {
    foreach ($values as $value) {
        $foo = 'foo' . $value;
    }
    echo $foo;
};

function ($values) {
    list($a, $b) = $values;
    return $a + $b;
};

function ($values) {
    [$c, $d] = $values;
    return $c * $d;
};

function ($values) {
    $current = 'current';
    $next = 'next';

    while ($next) {
        if ($current) {

        }

        $current = false;

        if (true) {
            foreach ($values as $value) {
                $next = $value;
            }
        }

        do {
            $previous = 'previous';
        } while ($previous);
    }
};

function (&$parameter) {
    $parameter = 'value-by-reference';
};

function () use (&$inheritedVariable) {
    $inheritedVariable = 'value-by-reference';
};

function ($interval) {
    $j = 0;
    for ($i = $j; $i < 10; $i += $interval) {
    }
};

function () {
    static $static = false;
    if ($static) {
        return;
    }

    $static = true;
};

function () {
    $a = 'a';
    $b = 'b';

    $this->compact;

    return compact('a', "b");
};

function () {
    $a = '';
    echo "$a";
};

function () {
    $a = '';
    echo "${a}";
};

function () {
    $a = '';
    echo "$a()";
};

function () {
    $a = '';
    echo <<<TEXT
	$a
TEXT;
};

function () {
    $a = '';
    echo <<<TEXT
	${a}
TEXT;
};

function () {
    $a = 10;
    max(1, $a += 10);
};

class Whatever
{

    public function listFunction($a, $b) {
        list($this->a, $this->b) = [$a, $b];
    }

}

function () {
    $i = 0;
    while ($i++ <= 10) {
    }
};

function () {
    $i = 0;
    do {
    } while (++$i <= 10);
};

function () {
    $i = 10;
    while ($i-- > 0) {
    }
};

function () {
    $i = 10;
    do {
    } while (--$i > 0);
};

function ($data) {
    $i = 0;
    $c = '';
    foreach ($data as $c) {
        $c = $i++;
    }
    echo $c;
};

function ($values) {
    $expectedKey = 0;

    foreach ($values as $key => $value) {
        if ($key !== $expectedKey++) {
            return $value;
        }
    }

    return null;
};
