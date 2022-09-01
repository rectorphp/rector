<?php

declare (strict_types=1);
namespace RectorPrefix202209\Doctrine\Inflector;

interface WordInflector
{
    public function inflect(string $word) : string;
}
