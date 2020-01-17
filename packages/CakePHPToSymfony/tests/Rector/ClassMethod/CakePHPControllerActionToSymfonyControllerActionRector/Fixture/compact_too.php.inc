<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerActionToSymfonyControllerActionRector\Fixture;

class CompactTooController extends \AppController
{
    public function index()
    {
        $value = 5;
        $this->set(compact('clientId', 'states', 'invoiceTemplates'));
    }
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerActionToSymfonyControllerActionRector\Fixture;

class CompactTooController extends \AppController
{
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        $value = 5;
        return $this->render('homepage/index.twig', ['clientId' => $clientId, 'states' => $states, 'invoiceTemplates' => $invoiceTemplates]);
    }
}

?>
