<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Doctrine\Inflector\Rules;

use function array_map;
use function implode;
use function preg_match;
class Patterns
{
    /** @var Pattern[] */
    private $patterns;
    /** @var string */
    private $regex;
    public function __construct(\RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern ...$patterns)
    {
        $this->patterns = $patterns;
        $patterns = \array_map(static function (\RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern $pattern) : string {
            return $pattern->getPattern();
        }, $this->patterns);
        $this->regex = '/^(?:' . \implode('|', $patterns) . ')$/i';
    }
    /**
     * @param string $word
     */
    public function matches($word) : bool
    {
        return \preg_match($this->regex, $word, $regs) === 1;
    }
}
