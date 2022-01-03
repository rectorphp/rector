<?php

declare (strict_types=1);
namespace RectorPrefix20220103\Doctrine\Inflector;

class NoopWordInflector implements \RectorPrefix20220103\Doctrine\Inflector\WordInflector
{
    public function inflect(string $word) : string
    {
        return $word;
    }
}
