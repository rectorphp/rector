<?php

declare (strict_types=1);
namespace RectorPrefix20210916\Doctrine\Inflector;

class NoopWordInflector implements \RectorPrefix20210916\Doctrine\Inflector\WordInflector
{
    /**
     * @param string $word
     */
    public function inflect($word) : string
    {
        return $word;
    }
}
