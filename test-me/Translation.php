<?php declare(strict_types=1);

namespace Buggy;

use stdClass;

class Translation
{
    private $translationsDictionary;

    public function setTranslationsDictionary(stdClass $translationsDictionary): void
    {
        $this->translationsDictionary = $translationsDictionary;
    }
}
