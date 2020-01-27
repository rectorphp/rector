<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerRedirectToSymfonyRector\Fixture;

class RedirectToController extends \AppController
{
    public function index()
    {
        $this->redirect([
            'controller' => 'DocumentVersions',
            'action' => 'getFile'
        ]);
    }
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerRedirectToSymfonyRector\Fixture;

class RedirectToController extends \AppController
{
    public function index()
    {
        return $this->redirectToRoute('document_versions_get_file');
    }
}

?>
