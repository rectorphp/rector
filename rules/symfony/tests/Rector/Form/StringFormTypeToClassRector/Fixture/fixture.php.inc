<?php

namespace Rector\Symfony\Tests\Rector\Form\StringFormTypeToClassRector\Fixture;

use Symfony\Component\Form\FormBuilder;

function stringFromType()
{
    $formBuilder = new FormBuilder();
    $formBuilder->add('task', 'form.type.text');

    $variable = 'form.type.text';
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\Form\StringFormTypeToClassRector\Fixture;

use Symfony\Component\Form\FormBuilder;

function stringFromType()
{
    $formBuilder = new FormBuilder();
    $formBuilder->add('task', \Symfony\Component\Form\Extension\Core\Type\TextType::class);

    $variable = 'form.type.text';
}

?>
