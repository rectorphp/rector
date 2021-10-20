<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Doctrine\Inflector\Rules;

use RectorPrefix20211020\Doctrine\Inflector\WordInflector;
use function preg_replace;
final class Transformation implements \RectorPrefix20211020\Doctrine\Inflector\WordInflector
{
    /** @var Pattern */
    private $pattern;
    /** @var string */
    private $replacement;
    public function __construct(\RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern $pattern, string $replacement)
    {
        $this->pattern = $pattern;
        $this->replacement = $replacement;
    }
    public function getPattern() : \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern
    {
        return $this->pattern;
    }
    public function getReplacement() : string
    {
        return $this->replacement;
    }
    /**
     * @param string $word
     */
    public function inflect($word) : string
    {
        return (string) \preg_replace($this->pattern->getRegex(), $this->replacement, $word);
    }
}
