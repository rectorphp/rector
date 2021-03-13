<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Fixture;

class Fixture
{
    /**
     * @api
     */
    public function run()
    {
        return $this->useMe();
    }

    public function useMe()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Fixture;

class Fixture
{
    /**
     * @api
     */
    public function run()
    {
        return $this->useMe();
    }

    private function useMe()
    {
    }
}

?>
