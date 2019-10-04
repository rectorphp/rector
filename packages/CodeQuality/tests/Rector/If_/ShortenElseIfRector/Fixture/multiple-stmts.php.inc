<?php

namespace Rector\Php\Tests\Rector\If_\ShortenElseIfRector\Fixture;

class MultipleStmtsClass
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
            }
            $this->doSomething();
        }
    }

    public function doSomething()
    {

    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\If_\ShortenElseIfRector\Fixture;

class MultipleStmtsClass
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
            }
            $this->doSomething();
        }
    }

    public function doSomething()
    {

    }
}

?>
