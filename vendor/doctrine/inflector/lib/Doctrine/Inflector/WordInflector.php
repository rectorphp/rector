<?php

declare (strict_types=1);
namespace RectorPrefix202503\Doctrine\Inflector;

interface WordInflector
{
    public function inflect(string $word) : string;
}
