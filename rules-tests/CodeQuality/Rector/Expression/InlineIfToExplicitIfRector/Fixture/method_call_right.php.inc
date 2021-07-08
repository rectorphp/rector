<?php

namespace Rector\Tests\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector\Fixture;

class MethodCallRight
{
    public function run()
    {
        $this->data === null || $this->execute();
    }

    public function run2()
    {
        $this->data === null && $this->execute();
    }

    private function execute()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector\Fixture;

class MethodCallRight
{
    public function run()
    {
        if ($this->data !== null) {
            $this->execute();
        }
    }

    public function run2()
    {
        if ($this->data === null) {
            $this->execute();
        }
    }

    private function execute()
    {
    }
}

?>
