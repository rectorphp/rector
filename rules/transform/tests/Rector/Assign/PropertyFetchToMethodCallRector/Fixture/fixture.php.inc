<?php

namespace Rector\Transform\Tests\Rector\Assign\PropertyFetchToMethodCallRector\Fixture;

use Rector\Transform\Tests\Rector\Assign\PropertyFetchToMethodCallRector\Source\Translator;

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

namespace Rector\Transform\Tests\Rector\Assign\PropertyFetchToMethodCallRector\Fixture;

use Rector\Transform\Tests\Rector\Assign\PropertyFetchToMethodCallRector\Source\Translator;

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
