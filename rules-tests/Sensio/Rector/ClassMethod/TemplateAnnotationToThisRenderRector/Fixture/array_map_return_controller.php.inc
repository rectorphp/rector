<?php declare (strict_types=1);

namespace AppBundle\Controller;

use Rector\Sensio\Tests\Rector\ClassMethod\TemplateAnnotationToThisRenderRector\Source\ClassToBeCreated;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ArrayMapReturnController extends AbstractController
{
    /**
     * @Template("items_template")
     */
    public function indexAction($items)
    {
        $filteredItems = array_map(function ($item) {
            return new ClassToBeCreated($item);
        }, $items);

        return compact('filteredItems');
    }
}

?>
-----
<?php declare (strict_types=1);

namespace AppBundle\Controller;

use Rector\Sensio\Tests\Rector\ClassMethod\TemplateAnnotationToThisRenderRector\Source\ClassToBeCreated;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ArrayMapReturnController extends AbstractController
{
    public function indexAction($items): \Symfony\Component\HttpFoundation\Response
    {
        $filteredItems = array_map(function ($item) {
            return new ClassToBeCreated($item);
        }, $items);

        return $this->render('items_template', ['filteredItems' => $filteredItems]);
    }
}

?>
