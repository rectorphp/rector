<?php

namespace Rector\Php72\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class Wordpress
{
    public function run()
    {
        $replace = create_function('$t', 'return $t || "0" === $t;');

        $php_with = 'abc';
        $function = create_function('$x', "return \$php_with.\$x;");

        $this->map_attrs_func = create_function('$k,$v', 'return "$k=\"$v\"";');

        $this->map_xmlns_func = create_function('$p,$n', '$xd = "xmlns"; if(strlen($n[0])>0) $xd .= ":{$n[0]}"; return "{$xd}=\"{$n[1]}\"";');
    }
}

?>
-----
<?php

namespace Rector\Php72\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class Wordpress
{
    public function run()
    {
        $replace = function ($t) {
            return $t || "0" === $t;
        };

        $php_with = 'abc';
        $function = function ($x) use ($php_with) {
            return $php_with . $x;
        };

        $this->map_attrs_func = function ($k, $v) {
            return "{$k}=\"{$v}\"";
        };

        $this->map_xmlns_func = function ($p, $n) {
            $xd = "xmlns";
            if (strlen($n[0]) > 0) {
                $xd .= ":{$n[0]}";
            }
            return "{$xd}=\"{$n[1]}\"";
        };
    }
}

?>
