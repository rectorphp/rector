<?php

\App::uses('NotificationListener', 'Event');

class WithoutNamespace
{
    public function run()
    {
        $values = new NotificationListener();
    }
}

?>
-----
<?php

use Event\NotificationListener;

class WithoutNamespace
{
    public function run()
    {
        $values = new NotificationListener();
    }
}

?>
