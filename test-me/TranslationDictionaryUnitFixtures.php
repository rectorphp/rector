<?php declare(strict_types=1);

namespace Buggy;

use stdClass;

final class TranslationDictionaryUnitFixtures
{
    public function run()
    {
        $enTranslation = new Translation();
        $enTranslation->setTranslationsDictionary(new stdClass());
    }
}
