<?php

namespace Rector\Tests\Transform\Rector\New_\NewArgToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\New_\NewArgToMethodCallRector\Source\SomeDotenv;

class SomeClass
{
    public function run()
    {
        $dotenv = new SomeDotenv(true);
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\New_\NewArgToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\New_\NewArgToMethodCallRector\Source\SomeDotenv;

class SomeClass
{
    public function run()
    {
        $dotenv = (new SomeDotenv())->usePutenv();
    }
}

?>
