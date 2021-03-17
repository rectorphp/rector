<?php

namespace Rector\Tests\Transform\Rector\Assign\PropertyFetchToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\Assign\PropertyFetchToMethodCallRector\Source\Translator;

final class CustomTranslator extends Translator
{
    public function fooMethod()
    {
        // get locale
        $locale = $this->locale;

        // update locale
        $this->locale = $locale;
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Assign\PropertyFetchToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\Assign\PropertyFetchToMethodCallRector\Source\Translator;

final class CustomTranslator extends Translator
{
    public function fooMethod()
    {
        // get locale
        $locale = $this->getLocale();

        // update locale
        $this->setLocale($locale);
    }
}

?>
