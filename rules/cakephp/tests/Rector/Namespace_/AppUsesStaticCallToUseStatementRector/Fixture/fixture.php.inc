<?php

namespace Rector\CakePHP\Tests\Rector\Namespace_\AppUsesStaticCallToUseStatementRector\Fixture;

\App::uses('NotificationListener', 'Event');

class Fixture
{
    public function run()
    {
        $values = new NotificationListener();
    }
}

?>
-----
<?php

namespace Rector\CakePHP\Tests\Rector\Namespace_\AppUsesStaticCallToUseStatementRector\Fixture;

use Event\NotificationListener;

class Fixture
{
    public function run()
    {
        $values = new NotificationListener();
    }
}

?>
