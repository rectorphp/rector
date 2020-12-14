<?php

namespace Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormBuilder;

class SomeAnotherControllerWithArgs extends Controller
{
    public function action(FormBuilder $builder)
    {
        $builder->add('someText', new SomeTypeWithCtor(1));
    }
}

class SomeTypeWithCtor
{
    /**
     * @var int
     */
    private $number;

    public function __construct(int $number)
    {
        $this->number = $number;
    }
}

?>
-----
<?php

namespace Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormBuilder;

class SomeAnotherControllerWithArgs extends Controller
{
    public function action(FormBuilder $builder)
    {
        $builder->add('someText', \Rector\Symfony3\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Fixture\SomeTypeWithCtor::class, ['number' => 1]);
    }
}

class SomeTypeWithCtor
{
    /**
     * @var int
     */
    private $number;
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $this->number = $options['number'];
    }
    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefaults(['number' => null]);
    }
}

?>
