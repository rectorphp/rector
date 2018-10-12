<?php declare(strict_types=1);

namespace Rector\Tests\Rector\CodeQuality\CombinedAssignRector\Wrong;

class Whatever
{
    public function __construct($parameter)
    {
        self::$a &= 2;
        static::$a |= 4;
        self::$$parameter .= '';
        parent::${'a'} /= 10;
        self::${'a'}[0] -= 100;
        Anything::$a **= 2;
        Something\Anything::$a %= 2;
        \Something\Anything::$a *= 1000;
        self::$a::$b += 4;
        $this::$a <<= 2;
        $this->a >>= 2;
        $this->$$parameter ^= 10;
        $this->{'a'} += 10;
        $this->${'a'}[0]->$$b[1][2]::$c[3][4][5]->{" $d"} *= 100;
        $this->something += 10;
    }
}
