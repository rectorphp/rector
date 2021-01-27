<?php

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToNewRector\Fixture;

use Rector\Transform\Tests\Rector\StaticCall\StaticCallToNewRector\Source\SomeJsonResponse;

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

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToNewRector\Fixture;

use Rector\Transform\Tests\Rector\StaticCall\StaticCallToNewRector\Source\SomeJsonResponse;

class SomeClass
{
    public function run()
    {
        $dotenv = new \Rector\Transform\Tests\Rector\StaticCall\StaticCallToNewRector\Source\SomeJsonResponse();
    }
}

?>
