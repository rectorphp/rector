<?php

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\WrapTransParameterNameRector\Fixture;

use Rector\NetteToSymfony\Tests\Rector\MethodCall\WrapTransParameterNameRector\Source\SomeTranslator;

final class SomeController
{
    public function run()
    {
        $translator = new SomeTranslator('');
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

use Rector\NetteToSymfony\Tests\Rector\MethodCall\WrapTransParameterNameRector\Source\SomeTranslator;

final class SomeController
{
    public function run()
    {
        $translator = new SomeTranslator('');
        $translated = $translator->trans(
            'Hello %name%',
            ['%name%' => 'Tom']
        );
    }
}

?>
