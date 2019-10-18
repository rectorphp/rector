<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ClassWithNamedService55Controller
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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ClassWithNamedService55Controller
{
    public function index()
    {
        return $this->render('class_with_named_service55/index.html.twig');
    }
}

?>
