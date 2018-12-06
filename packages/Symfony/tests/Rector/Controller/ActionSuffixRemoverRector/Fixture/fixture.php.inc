<?php

namespace Rector\Symfony\Tests\Rector\Controller\ActionSuffixRemoverRector\Wrong;

use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class AppController extends SymfonyController
{
    public function indexAction()
    {
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Controller\ActionSuffixRemoverRector\Wrong;

use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class AppController extends SymfonyController
{
    public function index()
    {
    }
}

?>
