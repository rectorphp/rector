<?php declare(strict_types=1);

namespace Buggy;

use stdClass;

/**
 * @ORM\Table(name="translations")
 * @ORM\Entity()
 */
class Translation
{
    /**
     * @var stdClass
     */
    private $translationsDictionary;

    public function setTranslationsDictionary(stdClass $translationsDictionary): void
    {
        $this->translationsDictionary = $translationsDictionary;
    }
}
