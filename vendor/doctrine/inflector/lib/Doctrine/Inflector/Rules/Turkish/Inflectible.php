<?php

declare (strict_types=1);
namespace RectorPrefix202411\Doctrine\Inflector\Rules\Turkish;

use RectorPrefix202411\Doctrine\Inflector\Rules\Pattern;
use RectorPrefix202411\Doctrine\Inflector\Rules\Substitution;
use RectorPrefix202411\Doctrine\Inflector\Rules\Transformation;
use RectorPrefix202411\Doctrine\Inflector\Rules\Word;
class Inflectible
{
    /** @return Transformation[] */
    public static function getSingular() : iterable
    {
        (yield new Transformation(new Pattern('/l[ae]r$/i'), ''));
    }
    /** @return Transformation[] */
    public static function getPlural() : iterable
    {
        (yield new Transformation(new Pattern('/([eöiü][^aoıueöiü]{0,6})$/u'), '\\1ler'));
        (yield new Transformation(new Pattern('/([aoıu][^aoıueöiü]{0,6})$/u'), '\\1lar'));
    }
    /** @return Substitution[] */
    public static function getIrregular() : iterable
    {
        (yield new Substitution(new Word('ben'), new Word('biz')));
        (yield new Substitution(new Word('sen'), new Word('siz')));
        (yield new Substitution(new Word('o'), new Word('onlar')));
    }
}
