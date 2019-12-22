<?php
namespace Rector\NetteToSymfony\Tests\Rector\Assign\FormControlToControllerAndFormTypeRector\Fixture;

class SomeFormController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function actionSomeForm(\Symfony\Component\HttpFoundation\Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $form = $this->createForm(\Rector\NetteToSymfony\Tests\Rector\Assign\FormControlToControllerAndFormTypeRector\Fixture\SomeFormType::class);
        $form->handleRequest($request);
        if ($form->isSuccess() && $form->isValid()) {
        }
    }
}
