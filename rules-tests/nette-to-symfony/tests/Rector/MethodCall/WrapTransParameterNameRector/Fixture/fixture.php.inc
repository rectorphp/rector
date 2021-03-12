<?php

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\WrapTransParameterNameRector\Fixture;

use Symfony\Component\Translation\Translator;

final class SomeController
{
    public function run()
    {
        $translator = new Translator('');
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

use Symfony\Component\Translation\Translator;

final class SomeController
{
    public function run()
    {
        $translator = new Translator('');
        $translated = $translator->trans(
            'Hello %name%',
            ['%name%' => 'Tom']
        );
    }
}

?>
