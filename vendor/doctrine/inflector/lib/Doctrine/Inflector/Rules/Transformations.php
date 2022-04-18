<?php

declare (strict_types=1);
namespace RectorPrefix20220418\Doctrine\Inflector\Rules;

use RectorPrefix20220418\Doctrine\Inflector\WordInflector;
class Transformations implements \RectorPrefix20220418\Doctrine\Inflector\WordInflector
{
    /** @var Transformation[] */
    private $transformations;
    public function __construct(\RectorPrefix20220418\Doctrine\Inflector\Rules\Transformation ...$transformations)
    {
        $this->transformations = $transformations;
    }
    public function inflect(string $word) : string
    {
        foreach ($this->transformations as $transformation) {
            if ($transformation->getPattern()->matches($word)) {
                return $transformation->inflect($word);
            }
        }
        return $word;
    }
}
