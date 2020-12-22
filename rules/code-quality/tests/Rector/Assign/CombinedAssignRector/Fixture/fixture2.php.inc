<?php

namespace Rector\CodeQuality\Tests\Rector\Assign\CombinedAssignRector\Fixture;

use Rector\CodeQuality\Tests\Rector\Assign\CombinedAssignRector\Source\AbstractSomeClassSoParentCanBeUsed;

class Whatever extends AbstractSomeClassSoParentCanBeUsed
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

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Assign\CombinedAssignRector\Fixture;

use Rector\CodeQuality\Tests\Rector\Assign\CombinedAssignRector\Source\AbstractSomeClassSoParentCanBeUsed;

class Whatever extends AbstractSomeClassSoParentCanBeUsed
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

?>
