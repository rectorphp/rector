<?php

declare (strict_types=1);
namespace RectorPrefix20220403\Doctrine\Inflector;

interface WordInflector
{
    public function inflect(string $word) : string;
}
