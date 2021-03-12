<?php

namespace Rector\Symfony3\Tests\Rector\MethodCall\OptionNameRector\Fixture;

use Rector\Symfony3\Tests\Rector\MethodCall\OptionNameRector\Source\AbstractType;
use Symfony\Component\Form\FormBuilder;

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

namespace Rector\Symfony3\Tests\Rector\MethodCall\OptionNameRector\Fixture;

use Rector\Symfony3\Tests\Rector\MethodCall\OptionNameRector\Source\AbstractType;
use Symfony\Component\Form\FormBuilder;

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
