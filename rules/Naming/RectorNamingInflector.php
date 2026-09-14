<?php

declare (strict_types=1);
namespace Rector\Naming;

use RectorPrefix202609\Doctrine\Inflector\Inflector;
use RectorPrefix202609\Nette\Utils\Strings;
final class RectorNamingInflector
{
    /**
     * @readonly
     */
    private Inflector $inflector;
    /**
     * @see https://regex101.com/r/VqVvke/3
     * @var string
     */
    private const DATA_INFO_SUFFIX_REGEX = '#^(?<prefix>.+)(?<suffix>Data|Info)$#';
    /**
     * Mass nouns ending in lowercase "data"/"info", eg "metadata", must stay untouched
     * @var string
     */
    private const MASS_NOUN_SUFFIX_REGEX = '#(?:data|info)$#';
    public function __construct(Inflector $inflector)
    {
        $this->inflector = $inflector;
    }
    public function singularize(string $name): string
    {
        $matches = Strings::match($name, self::DATA_INFO_SUFFIX_REGEX);
        if ($matches !== null) {
            $singularized = $this->inflector->singularize((string) $matches['prefix']);
            $uninflectable = $matches['suffix'];
            return $singularized . $uninflectable;
        }
        if (Strings::match($name, self::MASS_NOUN_SUFFIX_REGEX) !== null) {
            return $name;
        }
        return $this->inflector->singularize($name);
    }
}
