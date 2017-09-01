<?php declare (strict_types=1);

$form = new \Symfony\Component\Form\Form;

if ($form->isValid()) {
    $this->processForm($form);
}
