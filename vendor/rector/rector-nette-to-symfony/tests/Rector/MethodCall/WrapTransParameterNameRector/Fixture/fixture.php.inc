<?php

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\WrapTransParameterNameRector\Fixture;

use Rector\NetteToSymfony\Tests\Rector\MethodCall\WrapTransParameterNameRector\Source\LocalTranslator;

final class SomeController
{
    public function run()
    {
        $translator = new LocalTranslator('');
        $translated = $translator->trans(
            'Hello %name%',
            ['name' => 'Tom']
        );
    }
}

?>
-----
<?php

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\WrapTransParameterNameRector\Fixture;

use Rector\NetteToSymfony\Tests\Rector\MethodCall\WrapTransParameterNameRector\Source\LocalTranslator;

final class SomeController
{
    public function run()
    {
        $translator = new LocalTranslator('');
        $translated = $translator->trans(
            'Hello %name%',
            ['%name%' => 'Tom']
        );
    }
}

?>
