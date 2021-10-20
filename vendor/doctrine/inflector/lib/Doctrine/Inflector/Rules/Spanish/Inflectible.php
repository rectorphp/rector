<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Doctrine\Inflector\Rules\Spanish;

use RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern;
use RectorPrefix20211020\Doctrine\Inflector\Rules\Substitution;
use RectorPrefix20211020\Doctrine\Inflector\Rules\Transformation;
use RectorPrefix20211020\Doctrine\Inflector\Rules\Word;
class Inflectible
{
    /**
     * @return Transformation[]
     */
    public static function getSingular() : iterable
    {
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('/ereses$/'), 'erés'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('/iones$/'), 'ión'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('/ces$/'), 'z'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('/es$/'), ''));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('/s$/'), ''));
    }
    /**
     * @return Transformation[]
     */
    public static function getPlural() : iterable
    {
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('/ú([sn])$/i'), 'RectorPrefix20211020\\u\\1es'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('/ó([sn])$/i'), 'RectorPrefix20211020\\o\\1es'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('/í([sn])$/i'), 'RectorPrefix20211020\\i\\1es'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('/é([sn])$/i'), 'RectorPrefix20211020\\e\\1es'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('/á([sn])$/i'), 'RectorPrefix20211020\\a\\1es'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('/z$/i'), 'ces'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('/([aeiou]s)$/i'), '\\1'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('/([^aeéiou])$/i'), '\\1es'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('/$/'), 's'));
    }
    /**
     * @return Substitution[]
     */
    public static function getIrregular() : iterable
    {
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Word('el'), new \RectorPrefix20211020\Doctrine\Inflector\Rules\Word('los')));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Word('papá'), new \RectorPrefix20211020\Doctrine\Inflector\Rules\Word('papás')));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Word('mamá'), new \RectorPrefix20211020\Doctrine\Inflector\Rules\Word('mamás')));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Word('sofá'), new \RectorPrefix20211020\Doctrine\Inflector\Rules\Word('sofás')));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211020\Doctrine\Inflector\Rules\Word('mes'), new \RectorPrefix20211020\Doctrine\Inflector\Rules\Word('meses')));
    }
}
