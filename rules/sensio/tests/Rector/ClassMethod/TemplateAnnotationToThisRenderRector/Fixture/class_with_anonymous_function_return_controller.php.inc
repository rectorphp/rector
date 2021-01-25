<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ClassWithAnonymousFunctionReturnController extends AbstractController
{
    /**
     * @Template("items_template")
     */
    public function indexAction($items)
    {
        $filteredItems = array_filter($items, function ($items) {
            return count($items) > 0;
        });

        return compact('items');
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ClassWithAnonymousFunctionReturnController extends AbstractController
{
    public function indexAction($items): \Symfony\Component\HttpFoundation\Response
    {
        $filteredItems = array_filter($items, function ($items) {
            return count($items) > 0;
        });

        return $this->render('items_template', ['items' => $items]);
    }
}

?>
