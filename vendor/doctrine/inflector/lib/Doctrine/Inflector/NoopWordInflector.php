<?php

declare (strict_types=1);
namespace RectorPrefix202407\Doctrine\Inflector;

class NoopWordInflector implements WordInflector
{
    public function inflect(string $word) : string
    {
        return $word;
    }
}
