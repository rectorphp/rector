<?php

declare (strict_types=1);
namespace RectorPrefix202308\Doctrine\Inflector;

interface WordInflector
{
    public function inflect(string $word) : string;
}
