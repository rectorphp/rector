<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPBeforeFilterToRequestEventSubscriberRector\Fixture;

class SuperadminController extends \AppController
{
    public function beforeFilter()
    {
    	// something
    }
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPBeforeFilterToRequestEventSubscriberRector\Fixture;

class SuperadminController extends \AppController
{
}

?>
