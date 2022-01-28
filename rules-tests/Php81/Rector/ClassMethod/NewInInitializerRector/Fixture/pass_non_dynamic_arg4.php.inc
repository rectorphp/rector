<?php

namespace Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Fixture;

use stdClass;

class SomeClass
{
    private Logger $logger;

    public function __construct(
        ?Logger $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger([new stdClass]);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Fixture;

use stdClass;

class SomeClass
{
    public function __construct(private Logger $logger = new NullLogger([new stdClass]))
    {
    }
}

?>
