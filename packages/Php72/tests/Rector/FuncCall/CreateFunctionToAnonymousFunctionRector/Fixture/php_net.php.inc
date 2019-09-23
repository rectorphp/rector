<?php

namespace Rector\Php72\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class PhpNet
{
    public function run()
    {
        create_function('$x,$y', 'return "some trig: ".(sin($x) + $x*cos($y));');

        create_function('$x,$y', 'return "a hypotenuse: ".sqrt($x*$x + $y*$y);');

        create_function('$a,$b', 'if ($a >=0) {return "b*a^2 = ".$b*sqrt($a);} else {return false;}');

        create_function('$a,$b', "return \"min(b^2+a, a^2,b) = \".min(\$a*\$a+\$b,\$b*\$b+\$a);");

        create_function('$a,$b', 'if ($a > 0 && $b != 0) {return "ln(a)/b = ".log($a)/$b; } else { return false; }');

        create_function('$b,$a', 'if (strncmp($a, $b, 3) == 0) return "** \"$a\" '.
            'and \"$b\"** Look the same to me! (looking at the first 3 chars)";');

        create_function('$a,$b', '; return "CRCs: " . crc32($a) . ", ".crc32($b);');

        create_function('$a,$b', '; return "similar(a,b) = " . similar_text($a, $b, &$p) . "($p%)";');

        create_function('&$v,$k', '$v = $v . "mango";');
    }
}

?>
-----
<?php

namespace Rector\Php72\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class PhpNet
{
    public function run()
    {
        function ($x, $y) {
            return "some trig: " . (sin($x) + $x * cos($y));
        };

        function ($x, $y) {
            return "a hypotenuse: " . sqrt($x * $x + $y * $y);
        };

        function ($a, $b) {
            if ($a >= 0) {
                return "b*a^2 = " . $b * sqrt($a);
            } else {
                return false;
            }
        };

        function ($a, $b) {
            return "min(b^2+a, a^2,b) = " . min($a * $a + $b, $b * $b + $a);
        };

        function ($a, $b) {
            if ($a > 0 && $b != 0) {
                return "ln(a)/b = " . log($a) / $b;
            } else {
                return false;
            }
        };

        function ($b, $a) {
            if (strncmp($a, $b, 3) == 0) {
                return "** \"{$a}\" and \"{$b}\"** Look the same to me! (looking at the first 3 chars)";
            }
        };

        function ($a, $b) {
            return "CRCs: " . crc32($a) . ", " . crc32($b);
        };

        function ($a, $b) use ($p) {
            return "similar(a,b) = " . similar_text($a, $b, &$p) . "({$p}%)";
        };

        function (&$v, $k) {
            $v = $v . "mango";
        };
    }
}

?>
