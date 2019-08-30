<?php

namespace App\Controller;

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ClassWithNamedService55Controller extends SymfonyController
{
    /**
     * @Template()
     */
    public function index()
    {
        return [];
    }
}

?>
-----
<?php

namespace App\Controller;

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ClassWithNamedService55Controller extends SymfonyController
{
    public function index()
    {
        return $this->render('class_with_named_service55/index.html.twig');
    }
}

?>
