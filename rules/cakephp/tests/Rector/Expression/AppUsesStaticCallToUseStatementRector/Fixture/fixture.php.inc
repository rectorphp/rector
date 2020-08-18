<?php

namespace Rector\CakePHP\Tests\Rector\Expression\AppUsesStaticCallToUseStatementRector\Fixture;

\App::uses('NotificationListener', 'Event');

class SomeClass
{
    public function run()
    {
        $values = new NotificationListener();
    }
}

?>
-----
<?php

namespace Rector\CakePHP\Tests\Rector\Expression\AppUsesStaticCallToUseStatementRector\Fixture;

use Event\NotificationListener;

class SomeClass
{
    public function run()
    {
        $values = new NotificationListener();
    }
}

?>
