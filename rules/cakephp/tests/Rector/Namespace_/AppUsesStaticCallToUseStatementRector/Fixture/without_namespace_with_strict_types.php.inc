<?php

declare(strict_types=1);

\App::uses('NotificationListener', 'Event');

class WithoutNamespaceWithStrictTypes
{
    public function run()
    {
        $values = new NotificationListener();
    }
}

?>
-----
<?php

declare(strict_types=1);

use Event\NotificationListener;

class WithoutNamespaceWithStrictTypes
{
    public function run()
    {
        $values = new NotificationListener();
    }
}

?>
