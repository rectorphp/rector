<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Doctrine\Inflector;

class CachedWordInflector implements \RectorPrefix20211020\Doctrine\Inflector\WordInflector
{
    /** @var WordInflector */
    private $wordInflector;
    /** @var string[] */
    private $cache = [];
    public function __construct(\RectorPrefix20211020\Doctrine\Inflector\WordInflector $wordInflector)
    {
        $this->wordInflector = $wordInflector;
    }
    /**
     * @param string $word
     */
    public function inflect($word) : string
    {
        return $this->cache[$word] ?? ($this->cache[$word] = $this->wordInflector->inflect($word));
    }
}
