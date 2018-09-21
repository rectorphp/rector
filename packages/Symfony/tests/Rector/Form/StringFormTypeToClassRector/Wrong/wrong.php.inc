<?php declare (strict_types=1);

use Rector\Symfony\Tests\Rector\Form\StringFormTypeToClassRector\Source\FormBuilder;

$formBuilder = new FormBuilder();
$formBuilder->add('task', 'form.type.text');

$variable = 'form.type.text';
