<?php

namespace Rector\Symfony\Tests\Form\OptionNameRector\Wrong;

use Rector\Symfony\Tests\Rector\Form\OptionNameRector\Source\AbstractType;
use Rector\Symfony\Tests\Rector\Form\OptionNameRector\Source\FormBuilder;

class RegistrationFormType2 extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('price1', 'text', array(
            'label' => 'form.price1',
            'precision' => 3,
        ));
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Form\OptionNameRector\Wrong;

use Rector\Symfony\Tests\Rector\Form\OptionNameRector\Source\AbstractType;
use Rector\Symfony\Tests\Rector\Form\OptionNameRector\Source\FormBuilder;

class RegistrationFormType2 extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('price1', 'text', array(
            'label' => 'form.price1',
            'scale' => 3,
        ));
    }
}

?>
