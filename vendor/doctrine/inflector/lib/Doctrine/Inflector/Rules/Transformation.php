<?php

declare (strict_types=1);
namespace RectorPrefix202506\Doctrine\Inflector\Rules;

use RectorPrefix202506\Doctrine\Inflector\WordInflector;
use function preg_replace;
final class Transformation implements WordInflector
{
    /** @var Pattern */
    private $pattern;
    /** @var string */
    private $replacement;
    public function __construct(Pattern $pattern, string $replacement)
    {
        $this->pattern = $pattern;
        $this->replacement = $replacement;
    }
    public function getPattern() : Pattern
    {
        return $this->pattern;
    }
    public function getReplacement() : string
    {
        return $this->replacement;
    }
    public function inflect(string $word) : string
    {
        return (string) preg_replace($this->pattern->getRegex(), $this->replacement, $word);
    }
}
