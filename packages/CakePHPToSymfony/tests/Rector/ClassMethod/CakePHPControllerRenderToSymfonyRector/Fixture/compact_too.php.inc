<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerRenderToSymfonyRector\Fixture;

class CompactTooController extends \AppController
{
    public function index()
    {
        $this->set(compact('clientId', 'states', 'invoiceTemplates'));
    }
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerRenderToSymfonyRector\Fixture;

class CompactTooController extends \AppController
{
    public function index()
    {
        return $this->render('compacttoo/index.twig', ['clientId' => $clientId, 'states' => $states, 'invoiceTemplates' => $invoiceTemplates]);
    }
}

?>
