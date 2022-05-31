<?php

declare (strict_types=1);
namespace Rector\Naming;

use RectorPrefix20220531\Doctrine\Inflector\Inflector;
use RectorPrefix20220531\Nette\Utils\Strings;
final class RectorNamingInflector
{
    /**
     * @var string
     * @see https://regex101.com/r/VqVvke/3
     */
    private const DATA_INFO_SUFFIX_REGEX = '#^(?<prefix>.+)(?<suffix>Data|Info)$#';
    /**
     * @readonly
     * @var \Doctrine\Inflector\Inflector
     */
    private $inflector;
    public function __construct(\RectorPrefix20220531\Doctrine\Inflector\Inflector $inflector)
    {
        $this->inflector = $inflector;
    }
    public function singularize(string $name) : string
    {
        $matches = \RectorPrefix20220531\Nette\Utils\Strings::match($name, self::DATA_INFO_SUFFIX_REGEX);
        if ($matches === null) {
            return $this->inflector->singularize($name);
        }
        $singularized = $this->inflector->singularize($matches['prefix']);
        $uninflectable = $matches['suffix'];
        return $singularized . $uninflectable;
    }
}
