<?php

declare (strict_types=1);
namespace RectorPrefix20211231\Doctrine\Inflector\Rules\English;

use RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern;
use RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution;
use RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation;
use RectorPrefix20211231\Doctrine\Inflector\Rules\Word;
class Inflectible
{
    /**
     * @return Transformation[]
     */
    public static function getSingular() : iterable
    {
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(s)tatuses$'), 'RectorPrefix20211231\\1\\2tatus'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(s)tatus$'), 'RectorPrefix20211231\\1\\2tatus'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(c)ampus$'), 'RectorPrefix20211231\\1\\2ampus'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('^(.*)(menu)s$'), 'RectorPrefix20211231\\1\\2'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(quiz)zes$'), '\\1'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(matr)ices$'), '\\1ix'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(vert|ind)ices$'), '\\1ex'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('^(ox)en'), '\\1'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(alias)(es)*$'), '\\1'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(buffal|her|potat|tomat|volcan)oes$'), '\\1o'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$'), '\\1us'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('([ftw]ax)es'), '\\1'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(analys|ax|cris|test|thes)es$'), '\\1is'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(shoe|slave)s$'), '\\1'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(o)es$'), '\\1'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('ouses$'), 'ouse'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('([^a])uses$'), '\\1us'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('([m|l])ice$'), '\\1ouse'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(x|ch|ss|sh)es$'), '\\1'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(m)ovies$'), 'RectorPrefix20211231\\1\\2ovie'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(s)eries$'), 'RectorPrefix20211231\\1\\2eries'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('([^aeiouy]|qu)ies$'), '\\1y'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('([lr])ves$'), '\\1f'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(tive)s$'), '\\1'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(hive)s$'), '\\1'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(drive)s$'), '\\1'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(dive)s$'), '\\1'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(olive)s$'), '\\1'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('([^fo])ves$'), '\\1fe'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(^analy)ses$'), '\\1sis'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(analy|diagno|^ba|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$'), 'RectorPrefix20211231\\1\\2sis'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(tax)a$'), '\\1on'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(c)riteria$'), '\\1riterion'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('([ti])a$'), '\\1um'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(p)eople$'), 'RectorPrefix20211231\\1\\2erson'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(m)en$'), '\\1an'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(c)hildren$'), 'RectorPrefix20211231\\1\\2hild'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(f)eet$'), '\\1oot'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(n)ews$'), 'RectorPrefix20211231\\1\\2ews'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('eaus$'), 'eau'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('s$'), ''));
    }
    /**
     * @return Transformation[]
     */
    public static function getPlural() : iterable
    {
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(s)tatus$'), 'RectorPrefix20211231\\1\\2tatuses'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(quiz)$'), '\\1zes'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('^(ox)$'), 'RectorPrefix20211231\\1\\2en'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('([m|l])ouse$'), '\\1ice'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(matr|vert|ind)(ix|ex)$'), '\\1ices'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(x|ch|ss|sh)$'), '\\1es'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('([^aeiouy]|qu)y$'), '\\1ies'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(hive|gulf)$'), '\\1s'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(?:([^f])fe|([lr])f)$'), 'RectorPrefix20211231\\1\\2ves'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('sis$'), 'ses'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('([ti])um$'), '\\1a'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(tax)on$'), '\\1a'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(c)riterion$'), '\\1riteria'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(p)erson$'), '\\1eople'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(m)an$'), '\\1en'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(c)hild$'), '\\1hildren'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(f)oot$'), '\\1eet'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(buffal|her|potat|tomat|volcan)o$'), 'RectorPrefix20211231\\1\\2oes'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|vir)us$'), '\\1i'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('us$'), 'uses'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(alias)$'), '\\1es'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('(analys|ax|cris|test|thes)is$'), '\\1es'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('s$'), 's'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('^$'), ''));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Transformation(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('$'), 's'));
    }
    /**
     * @return Substitution[]
     */
    public static function getIrregular() : iterable
    {
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('atlas'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('atlases')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('axe'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('axes')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('beef'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('beefs')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('brother'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('brothers')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('cafe'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('cafes')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('chateau'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('chateaux')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('niveau'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('niveaux')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('child'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('children')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('canvas'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('canvases')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('cookie'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('cookies')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('corpus'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('corpuses')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('cow'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('cows')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('criterion'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('criteria')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('curriculum'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('curricula')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('demo'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('demos')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('domino'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('dominoes')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('echo'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('echoes')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('foot'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('feet')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('fungus'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('fungi')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('ganglion'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('ganglions')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('gas'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('gases')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('genie'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('genies')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('genus'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('genera')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('goose'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('geese')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('graffito'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('graffiti')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('hippopotamus'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('hippopotami')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('hoof'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('hoofs')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('human'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('humans')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('iris'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('irises')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('larva'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('larvae')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('leaf'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('leaves')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('lens'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('lenses')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('loaf'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('loaves')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('man'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('men')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('medium'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('media')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('memorandum'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('memoranda')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('money'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('monies')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('mongoose'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('mongooses')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('motto'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('mottoes')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('move'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('moves')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('mythos'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('mythoi')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('niche'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('niches')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('nucleus'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('nuclei')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('numen'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('numina')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('occiput'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('occiputs')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('octopus'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('octopuses')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('opus'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('opuses')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('ox'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('oxen')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('passerby'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('passersby')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('penis'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('penises')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('person'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('people')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('plateau'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('plateaux')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('runner-up'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('runners-up')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('safe'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('safes')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('sex'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('sexes')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('soliloquy'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('soliloquies')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('son-in-law'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('sons-in-law')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('syllabus'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('syllabi')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('testis'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('testes')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('thief'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('thieves')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('tooth'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('teeth')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('tornado'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('tornadoes')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('trilby'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('trilbys')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('turf'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('turfs')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('valve'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('valves')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('volcano'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('volcanoes')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('abuse'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('abuses')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('avalanche'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('avalanches')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('cache'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('caches')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('criterion'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('criteria')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('curve'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('curves')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('emphasis'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('emphases')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('foe'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('foes')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('grave'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('graves')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('hoax'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('hoaxes')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('medium'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('media')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('neurosis'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('neuroses')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('save'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('saves')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('wave'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('waves')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('oasis'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('oases')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('valve'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('valves')));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Substitution(new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('zombie'), new \RectorPrefix20211231\Doctrine\Inflector\Rules\Word('zombies')));
    }
}
