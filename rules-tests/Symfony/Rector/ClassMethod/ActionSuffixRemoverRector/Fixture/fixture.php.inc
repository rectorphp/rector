<?php

namespace Rector\Tests\Symfony\Rector\ClassMethod\ActionSuffixRemoverRector\Fixture;

use Rector\Tests\Symfony\Rector\Source\SymfonyController;

class AppController extends SymfonyController
{
    public function indexAction()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Symfony\Rector\ClassMethod\ActionSuffixRemoverRector\Fixture;

use Rector\Tests\Symfony\Rector\Source\SymfonyController;

class AppController extends SymfonyController
{
    public function index()
    {
    }
}

?>
