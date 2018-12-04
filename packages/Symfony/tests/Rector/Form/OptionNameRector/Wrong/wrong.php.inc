<?php

namespace Rector\Symfony\Tests\Form\OptionNameRector\Wrong;

use Rector\Symfony\Tests\Rector\Form\OptionNameRector\Source\AbstractType;
use Rector\Symfony\Tests\Rector\Form\OptionNameRector\Source\FormBuilder;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'form.name'))
            ->add('price1', 'text', array(
                'label' => 'form.price1',
                'precision' => 3,
            ))
            ->add('price2', 'text', array(
                'precision' => 3,
            ))
            ->add('discount', 'integer', [
                'label' => 'form.email',
                'virtual' => true,
            ])
            ->add('password', 'password')
        ;
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Form\OptionNameRector\Wrong;

use Rector\Symfony\Tests\Rector\Form\OptionNameRector\Source\AbstractType;
use Rector\Symfony\Tests\Rector\Form\OptionNameRector\Source\FormBuilder;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'form.name'))
            ->add('price1', 'text', array(
                'label' => 'form.price1',
                'scale' => 3,
            ))
            ->add('price2', 'text', array(
                'scale' => 3,
            ))
            ->add('discount', 'integer', [
                'label' => 'form.email',
                'inherit_data' => true,
            ])
            ->add('password', 'password')
        ;
    }
}

?>
