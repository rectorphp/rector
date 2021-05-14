<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\FormControlToControllerAndFormTypeRector\Fixture;

class SomeFormController extends \RectorPrefix20210514\Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function actionSomeForm(\RectorPrefix20210514\Symfony\Component\HttpFoundation\Request $request) : \RectorPrefix20210514\Symfony\Component\HttpFoundation\Response
    {
        $form = $this->createForm(\Rector\NetteToSymfony\Tests\Rector\Class_\FormControlToControllerAndFormTypeRector\Fixture\SomeFormType::class);
        $form->handleRequest($request);
        if ($form->isSuccess() && $form->isValid()) {
        }
    }
}
