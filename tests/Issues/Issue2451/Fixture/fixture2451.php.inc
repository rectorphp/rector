<?php

namespace Rector\Tests\Issues\Issue2451\Fixture;

use Rector\Tests\Issues\Issue2451\Source\AbstractBaseHandler;

class Issue2451 extends AbstractBaseHandler
{
    public function handle($params)
    {
        // do something
    }
}

?>
-----
<?php

namespace Rector\Tests\Issues\Issue2451\Fixture;

use Rector\Tests\Issues\Issue2451\Source\AbstractBaseHandler;

class Issue2451 extends AbstractBaseHandler
{
    public function handle($params): \Tomaj\NetteApi\Response\ResponseInterface
    {
        // do something
    }
}

?>
