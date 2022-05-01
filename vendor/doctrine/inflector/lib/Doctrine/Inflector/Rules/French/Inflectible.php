<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Doctrine\Inflector\Rules\French;

use RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern;
use RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution;
use RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation;
use RectorPrefix20220501\Doctrine\Inflector\Rules\Word;
class Inflectible
{
    /**
     * @return Transformation[]
     */
    public static function getSingular() : iterable
    {
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/(b|cor|ém|gemm|soupir|trav|vant|vitr)aux$/'), '\\1ail'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/ails$/'), 'ail'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/(journ|chev)aux$/'), '\\1al'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/(bijou|caillou|chou|genou|hibou|joujou|pou|au|eu|eau)x$/'), '\\1'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/s$/'), ''));
    }
    /**
     * @return Transformation[]
     */
    public static function getPlural() : iterable
    {
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/(s|x|z)$/'), '\\1'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/(b|cor|ém|gemm|soupir|trav|vant|vitr)ail$/'), '\\1aux'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/ail$/'), 'ails'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/(chacal|carnaval|festival|récital)$/'), '\\1s'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/al$/'), 'aux'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/(bleu|émeu|landau|pneu|sarrau)$/'), '\\1s'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/(bijou|caillou|chou|genou|hibou|joujou|lieu|pou|au|eu|eau)$/'), '\\1x'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/$/'), 's'));
    }
    /**
     * @return Substitution[]
     */
    public static function getIrregular() : iterable
    {
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('monsieur'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('messieurs')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('madame'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('mesdames')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('mademoiselle'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('mesdemoiselles')));
    }
}
