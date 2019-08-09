<?php declare(strict_types=1);

namespace Buggy;

use stdClass;

final class TranslationDictionaryUnitFixtures
{
    public function getTranslationDictionary()
    {
        $translationDictionary = new stdClass();

        $enTranslation = new Translation();
        $enTranslation->setTranslationsDictionary($translationDictionary);

        $csTranslation = new Translation();
        $csTranslation->setTranslationsDictionary($translationDictionary);
    }
}
