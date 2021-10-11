<?php

namespace Rector\Tests\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector\Fixture;

class Stringable {
    private string $string = '';

    public function __toString() : string {
        return $this->string;
    }
}

class StringableObject
{
    public function run()
    {
        $value = new Stringable();

        $empty = strlen($value) === 0;

        $empty = 0 === strlen($value);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector\Fixture;

class Stringable {
    private string $string = '';

    public function __toString() : string {
        return $this->string;
    }
}

class StringableObject
{
    public function run()
    {
        $value = new Stringable();

        $empty = (string) $value === '';

        $empty = (string) $value === '';
    }
}

?>
