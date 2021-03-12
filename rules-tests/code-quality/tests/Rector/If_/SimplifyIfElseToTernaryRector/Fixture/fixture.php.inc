<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfElseToTernaryRector\Fixture;

class Fixture
{
    public function run()
    {
        if (empty($value)) {
            $this->arrayBuilt[][$key] = true;
        } else {
            $this->arrayBuilt[][$key] = $value;
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfElseToTernaryRector\Fixture;

class Fixture
{
    public function run()
    {
        $this->arrayBuilt[][$key] = empty($value) ? true : $value;
    }
}

?>
