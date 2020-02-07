<?php

namespace Rector\Symfony\Tests\Rector\Controller\ActionSuffixRemoverRector\Fixture;

use Rector\Symfony\Tests\Rector\Source\AbstractSymfonyController;

class AppController2 extends AbstractSymfonyController
{
    public function indexAction()
    {
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Controller\ActionSuffixRemoverRector\Fixture;

use Rector\Symfony\Tests\Rector\Source\AbstractSymfonyController;

class AppController2 extends AbstractSymfonyController
{
    public function index()
    {
    }
}

?>
