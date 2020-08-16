<?php

use Rector\Generic\Tests\Rector\Property\PropertyToMethodRector\Source\Translator;

class CustomTranslator extends Translator
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

use Rector\Generic\Tests\Rector\Property\PropertyToMethodRector\Source\Translator;

class CustomTranslator extends Translator
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
