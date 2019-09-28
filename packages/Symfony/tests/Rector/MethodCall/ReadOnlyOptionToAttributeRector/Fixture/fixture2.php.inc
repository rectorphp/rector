<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\ReadOnlyOptionToAttributeRector;

use Symfony\Component\Form\FormBuilder;

class ReadOnlyOptionToAttribute2
{
    function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('cuid', TextType::class, ['read_only' => true, 'attr' => ['one' => 'two']]);
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\ReadOnlyOptionToAttributeRector;

use Symfony\Component\Form\FormBuilder;

class ReadOnlyOptionToAttribute2
{
    function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('cuid', TextType::class, ['attr' => ['one' => 'two', 'readonly' => true]]);
    }
}

?>
