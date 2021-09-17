<?php

declare (strict_types=1);
namespace RectorPrefix20210917\Doctrine\Inflector;

class NoopWordInflector implements \RectorPrefix20210917\Doctrine\Inflector\WordInflector
{
    /**
     * @param string $word
     */
    public function inflect($word) : string
    {
        return $word;
    }
}
