<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Wrong;

use Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\ControllerClass;

class SomeController extends ControllerClass
{
    public function action()
    {
        $form = $this->createForm(new AnotherFormType(), $entity, [
            'action' => $this->generateUrl('teams_update', ['id' => $entity->getId()]),
            'method' => 'PUT',
        ]);

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

namespace Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Wrong;

use Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\ControllerClass;

class SomeController extends ControllerClass
{
    public function action()
    {
        $form = $this->createForm(AnotherFormType::class, $entity, [
            'action' => $this->generateUrl('teams_update', ['id' => $entity->getId()]),
            'method' => 'PUT',
        ]);

        $form = $this->createForm(AnotherFormType::class, $entity, ['items' => [1]]);
    }
}

final class AnotherFormType
{
    /**
     * @var array
     */
    private $items;
    public function buildForm(\Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\FormBuilder $builder, array $options)
    {
        $this->items = $options['items'];
    }
    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        $resolver->setDefaults(['items' => null]);
    }
}

?>
