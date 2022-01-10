<?php

declare (strict_types=1);
namespace RectorPrefix20220110\Doctrine\Inflector;

interface WordInflector
{
    public function inflect(string $word) : string;
}
