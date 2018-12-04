<?php

use Rector\Symfony\Tests\Rector\Form\StringFormTypeToClassRector\Source\FormBuilder;

function stringFromType()
{
    $formBuilder = new FormBuilder();
    $formBuilder->add('task', 'form.type.text');

    $variable = 'form.type.text';
}

?>
-----
<?php

use Rector\Symfony\Tests\Rector\Form\StringFormTypeToClassRector\Source\FormBuilder;

function stringFromType()
{
    $formBuilder = new FormBuilder();
    $formBuilder->add('task', \Symfony\Component\Form\Extension\Core\Type\TextType::class);

    $variable = 'form.type.text';
}

?>
