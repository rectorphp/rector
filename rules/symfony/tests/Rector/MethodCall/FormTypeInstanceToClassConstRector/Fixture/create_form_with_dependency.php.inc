<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Fixture;

use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PassingOptionsDependencyController extends Controller
{
    public function action()
    {
        $entity = new stdClass();
        $form = $this->createForm(new AnotherFormType([1]), $entity);
    }
}

final class AnotherFormType
{
    /**
     * @var array
     */
    private $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Fixture;

use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PassingOptionsDependencyController extends Controller
{
    public function action()
    {
        $entity = new stdClass();
        $form = $this->createForm(AnotherFormType::class, $entity, ['items' => [1]]);
    }
}

final class AnotherFormType
{
    /**
     * @var array
     */
    private $items;
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $this->items = $options['items'];
    }
    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefaults(['items' => null]);
    }
}

?>
