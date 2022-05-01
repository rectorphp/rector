<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Doctrine\Inflector\Rules\Portuguese;

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
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/^(g|)ases$/i'), '\\1ás'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/(japon|escoc|ingl|dinamarqu|fregu|portugu)eses$/i'), '\\1ês'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/(ae|ao|oe)s$/'), 'ao'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/(ãe|ão|õe)s$/'), 'ão'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/^(.*[^s]s)es$/i'), '\\1'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/sses$/i'), 'sse'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/ns$/i'), 'm'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/(r|t|f|v)is$/i'), '\\1il'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/uis$/i'), 'ul'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/ois$/i'), 'ol'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/eis$/i'), 'ei'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/éis$/i'), 'el'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/([^p])ais$/i'), '\\1al'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/(r|z)es$/i'), '\\1'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/^(á|gá)s$/i'), '\\1s'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/([^ê])s$/i'), '\\1'));
    }
    /**
     * @return Transformation[]
     */
    public static function getPlural() : iterable
    {
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/^(alem|c|p)ao$/i'), '\\1aes'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/^(irm|m)ao$/i'), '\\1aos'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/ao$/i'), 'oes'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/^(alem|c|p)ão$/i'), '\\1ães'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/^(irm|m)ão$/i'), '\\1ãos'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/ão$/i'), 'ões'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/^(|g)ás$/i'), '\\1ases'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/^(japon|escoc|ingl|dinamarqu|fregu|portugu)ês$/i'), '\\1eses'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/m$/i'), 'ns'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/([^aeou])il$/i'), '\\1is'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/ul$/i'), 'uis'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/ol$/i'), 'ois'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/el$/i'), 'eis'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/al$/i'), 'ais'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/(z|r)$/i'), '\\1es'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/(s)$/i'), '\\1'));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Pattern('/$/'), 's'));
    }
    /**
     * @return Substitution[]
     */
    public static function getIrregular() : iterable
    {
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('abdomen'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('abdomens')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('alemão'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('alemães')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('artesã'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('artesãos')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('álcool'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('álcoois')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('árvore'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('árvores')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('bencão'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('bencãos')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('cão'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('cães')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('campus'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('campi')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('cadáver'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('cadáveres')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('capelão'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('capelães')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('capitão'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('capitães')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('chão'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('chãos')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('charlatão'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('charlatães')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('cidadão'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('cidadãos')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('consul'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('consules')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('cristão'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('cristãos')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('difícil'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('difíceis')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('email'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('emails')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('escrivão'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('escrivães')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('fóssil'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('fósseis')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('gás'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('gases')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('germens'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('germen')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('grão'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('grãos')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('hífen'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('hífens')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('irmão'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('irmãos')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('liquens'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('liquen')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('mal'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('males')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('mão'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('mãos')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('orfão'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('orfãos')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('país'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('países')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('pai'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('pais')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('pão'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('pães')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('projétil'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('projéteis')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('réptil'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('répteis')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('sacristão'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('sacristães')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('sotão'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('sotãos')));
        (yield new \RectorPrefix20220501\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('tabelião'), new \RectorPrefix20220501\Doctrine\Inflector\Rules\Word('tabeliães')));
    }
}
