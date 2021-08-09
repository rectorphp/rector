<?php

declare (strict_types=1);
namespace RectorPrefix20210809\Doctrine\Inflector;

interface WordInflector
{
    /**
     * @param string $word
     */
    public function inflect($word) : string;
}
