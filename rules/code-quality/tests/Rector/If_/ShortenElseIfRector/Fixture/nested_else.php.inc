<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ShortenElseIfRector\Fixture;

class NestedElse
{
    /**
     * @var bool
     */
    private $cond1;

    /**
     * @var bool
     */
    private $cond2;

    public function run()
    {
        if ($this->cond1) {
            $this->doSomething();
        } else {
            if ($this->cond2) {
                $this->doSomething();
            } else {
                $this->doSomething();
            }
        }
    }

    public function doSomething()
    {

    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ShortenElseIfRector\Fixture;

class NestedElse
{
    /**
     * @var bool
     */
    private $cond1;

    /**
     * @var bool
     */
    private $cond2;

    public function run()
    {
        if ($this->cond1) {
            $this->doSomething();
        } elseif ($this->cond2) {
            $this->doSomething();
        } else {
            $this->doSomething();
        }
    }

    public function doSomething()
    {

    }
}

?>
