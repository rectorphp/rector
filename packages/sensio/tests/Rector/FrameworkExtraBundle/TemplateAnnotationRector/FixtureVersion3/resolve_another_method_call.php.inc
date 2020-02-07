<?php declare (strict_types=1);

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Fixture\Version3;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ResolveAnotherMethodCall extends AbstractController
{
    /**
     * @Template("PAPPSurveyBundle:Survey:create.html.twig")
     */
    public function createAction(Request $request)
    {
        return $this->processForm($request);
    }

    private function processForm(Request $request): array
    {
        return [
            'form' => $request,
        ];
    }
}

?>
-----
<?php declare (strict_types=1);

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Fixture\Version3;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ResolveAnotherMethodCall extends AbstractController
{
    public function createAction(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        return $this->processForm($request);
    }

    private function processForm(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('PAPPSurveyBundle:Survey:create.html.twig', [
            'form' => $request,
        ]);
    }
}

?>
