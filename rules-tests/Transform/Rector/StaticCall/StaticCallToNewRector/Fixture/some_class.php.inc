<?php

namespace Rector\Tests\Transform\Rector\StaticCall\StaticCallToNewRector\Fixture;

use Rector\Tests\Transform\Rector\StaticCall\StaticCallToNewRector\Source\SomeJsonResponse;

class SomeClass
{
    public function run()
    {
        $dotenv = SomeJsonResponse::create(true);
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\StaticCall\StaticCallToNewRector\Fixture;

use Rector\Tests\Transform\Rector\StaticCall\StaticCallToNewRector\Source\SomeJsonResponse;

class SomeClass
{
    public function run()
    {
        $dotenv = new \Rector\Tests\Transform\Rector\StaticCall\StaticCallToNewRector\Source\SomeJsonResponse();
    }
}

?>
