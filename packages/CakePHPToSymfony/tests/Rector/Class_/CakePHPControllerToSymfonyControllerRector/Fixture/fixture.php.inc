<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPControllerToSymfonyControllerRector\Fixture;

class HomepageController extends \AppController
{
    public function index()
    {
        $value = 5;
        $this->set('name', $value);
    }
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPControllerToSymfonyControllerRector\Fixture;

class HomepageController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        $value = 5;
        return $this->renderResponse('homepage/index.twig', [
            'name' => $value
        ]);
    }
}

?>
