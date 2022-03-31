<?php

declare (strict_types=1);
namespace RectorPrefix20220331\Doctrine\Inflector;

interface WordInflector
{
    public function inflect(string $word) : string;
}
