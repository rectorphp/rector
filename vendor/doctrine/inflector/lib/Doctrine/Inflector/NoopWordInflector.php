<?php

declare (strict_types=1);
namespace RectorPrefix20211015\Doctrine\Inflector;

class NoopWordInflector implements \RectorPrefix20211015\Doctrine\Inflector\WordInflector
{
    /**
     * @param string $word
     */
    public function inflect($word) : string
    {
        return $word;
    }
}
