<?php

declare (strict_types=1);
namespace RectorPrefix20220418\Doctrine\Inflector;

class NoopWordInflector implements \RectorPrefix20220418\Doctrine\Inflector\WordInflector
{
    public function inflect(string $word) : string
    {
        return $word;
    }
}
