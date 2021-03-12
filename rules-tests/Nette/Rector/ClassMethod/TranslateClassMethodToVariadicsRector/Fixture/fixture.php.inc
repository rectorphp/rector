<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TranslateClassMethodToVariadicsRector\Fixture;

use Nette\Localization\ITranslator;

final class SomeClass implements ITranslator
{
    public function translate($message, $count = null)
    {
        return [$message, $count];
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\TranslateClassMethodToVariadicsRector\Fixture;

use Nette\Localization\ITranslator;

final class SomeClass implements ITranslator
{
    public function translate($message, ...$parameters)
    {
        $count = $parameters[0] ?? null;
        return [$message, $count];
    }
}

?>
