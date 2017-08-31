<?php declare (strict_types=1);

$formBuilder = new Symfony\Component\Form\FormBuilder;
$formBuilder->add('task', \Symfony\Component\Form\Extension\Core\Type\TextType::class);
