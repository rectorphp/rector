<?php declare(strict_types=1);

namespace Rector\Tests\Rector\CodeQuality\CombinedAssignRector\Wrong;

class Whatever
{
    public function __construct($parameter)
    {
        self::$a = self::$a & 2;
        static::$a = static::$a | 4;
        self::$$parameter = self::$$parameter . '';
        parent::${'a'} = parent::${'a'} / 10;
        self::${'a'}[0] = self::${'a'}[0] - 100;
        Anything::$a = Anything::$a ** 2;
        Something\Anything::$a = Something\Anything::$a % 2;
        \Something\Anything::$a = \Something\Anything::$a * 1000;
        self::$a::$b = self::$a::$b + 4;
        $this::$a = $this::$a << 2;
        $this->a = $this->a >> 2;
        $this->$$parameter = $this->$$parameter ^ 10;
        $this->{'a'} = $this->{'a'} + 10;
        $this->${'a'}[0]->$$b[1][2]::$c[3][4][5]->{" $d"} = $this->${'a'}[0]->$$b[1][2]::$c[3][4][5]->{" $d"} * 100;
        $this->something = $this->something + 10;
    }
}
