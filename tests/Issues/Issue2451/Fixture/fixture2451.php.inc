<?php

namespace Rector\Core\Tests\Issues\Issue2451\Fixture;

use Rector\Core\Tests\Issues\Issue2451\Source\AbstractBaseHandler;

class Issue2451 extends AbstractBaseHandler
{
    public function handle()
    {
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue2451\Fixture;

use Rector\Core\Tests\Issues\Issue2451\Source\AbstractBaseHandler;

class Issue2451 extends AbstractBaseHandler
{
    public function handle(): \Tomaj\NetteApi\Response\ResponseInterface
    {
    }
}

?>
