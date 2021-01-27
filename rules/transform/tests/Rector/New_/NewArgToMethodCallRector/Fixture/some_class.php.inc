<?php

namespace Rector\Transform\Tests\Rector\New_\NewArgToMethodCallRector\Fixture;

use Rector\Transform\Tests\Rector\New_\NewArgToMethodCallRector\Source\SomeDotenv;

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

namespace Rector\Transform\Tests\Rector\New_\NewArgToMethodCallRector\Fixture;

use Rector\Transform\Tests\Rector\New_\NewArgToMethodCallRector\Source\SomeDotenv;

class SomeClass
{
    public function run()
    {
        $dotenv = (new SomeDotenv())->usePutenv();
    }
}

?>
