<?php

namespace Rector\Symfony3\Tests\Rector\ClassMethod\GetRequestRector\Fixture;

use Rector\Symfony3\Tests\Rector\Source\SymfonyController;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Captcha;
use App\Form\CaptchaType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class CaptchaController extends SymfonyController
{
    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        get('request');

        $form = $this->createForm(new CaptchaType(), new Captcha());
        $form->add('Enregistrer', 'submit', [
            'attr' => ['class' => 'btn btn-default'],
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($form->getData());
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                'Le captcha a bien été ajouté.'
            );

            return $this->redirect($this->generateUrl('captcha'));
        }

        return $this->render('App:Captcha:add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param mixed $id
     */
    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $captcha = $em->getRepository('App:Captcha')->find($id);
        if (!$captcha) {
            throw new \Exception('Le Captcha n’existe pas.');
        }
        $form = $this->createForm(new CaptchaType(), $captcha);
        $form->add('Enregistrer', 'submit', [
            'attr' => ['class' => 'btn btn-default']
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                'Le captcha a bien été modifié.'
            );

            return $this->redirect($this->generateUrl('captcha'));
        }

        return $this->render('App:Captcha:edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $queryBuilder = $em->createQueryBuilder()
            ->select('c')
            ->from('App:Captcha', 'c')
        ;

        $captchas = $queryBuilder->getQuery()->getResult();

        return $this->render('App:Captcha:index.html.twig', [
            'captchas' => $captchas,
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param mixed $id
     */
    public function removeAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $captcha = $em->getRepository('App:Captcha')->find($id);
        if (!$captcha) {
            throw new \Exception('Le captcha demandé n’existe pas.');
        }

        $em->remove($captcha);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            'Le captcha a bien été supprimé.'
        );

        return $this->redirect($this->generateUrl('captcha'));
    }
}
