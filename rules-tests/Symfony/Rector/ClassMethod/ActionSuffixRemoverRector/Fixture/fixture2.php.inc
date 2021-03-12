<?php

namespace Rector\Tests\Symfony\Rector\ClassMethod\ActionSuffixRemoverRector\Fixture;

use Rector\Tests\Symfony\Rector\Source\AbstractSymfonyController;

class AppController2 extends AbstractSymfonyController
{
    public function indexAction()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Symfony\Rector\ClassMethod\ActionSuffixRemoverRector\Fixture;

use Rector\Tests\Symfony\Rector\Source\AbstractSymfonyController;

class AppController2 extends AbstractSymfonyController
{
    public function index()
    {
    }
}

?>
