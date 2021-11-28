<?php

namespace Rector\Tests\Transform\Rector\StaticCall\StaticCallToNewRector\Fixture;

use Rector\Tests\Transform\Rector\StaticCall\StaticCallToNewRector\Source\SomeJsonResponse;

class SomeClass
{
    public function run()
    {
        $dotenv = SomeJsonResponse::create(['foo' => 'bar'], Response::HTTP_OK);
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
        $dotenv = new \Rector\Tests\Transform\Rector\StaticCall\StaticCallToNewRector\Source\SomeJsonResponse(['foo' => 'bar'], Response::HTTP_OK);
    }
}

?>
