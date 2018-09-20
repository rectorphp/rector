<?php declare (strict_types=1);

use Rector\Symfony\Tests\Rector\Form\StringFormTypeToClassRector\Source\FormBuilder;

$formBuilder = new FormBuilder();
$formBuilder->add('task', \Symfony\Component\Form\Extension\Core\Type\TextType::class);

$variable = 'form.type.text';
