<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Doctrine\Inflector\Rules\English;

use RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern;
final class Uninflected
{
    /**
     * @return Pattern[]
     */
    public static function getSingular() : iterable
    {
        yield from self::getDefault();
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('.*ss'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('clothes'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('data'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('fascia'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('fuchsia'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('galleria'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('mafia'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('militia'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('pants'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('petunia'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('sepia'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('trivia'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('utopia'));
    }
    /**
     * @return Pattern[]
     */
    public static function getPlural() : iterable
    {
        yield from self::getDefault();
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('people'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('trivia'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('\\w+ware$'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('media'));
    }
    /**
     * @return Pattern[]
     */
    private static function getDefault() : iterable
    {
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('\\w+media'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('advice'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('aircraft'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('amoyese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('art'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('audio'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('baggage'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('bison'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('borghese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('bream'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('breeches'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('britches'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('buffalo'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('butter'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('cantus'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('carp'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('chassis'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('clippers'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('clothing'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('coal'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('cod'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('coitus'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('compensation'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('congoese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('contretemps'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('coreopsis'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('corps'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('cotton'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('data'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('debris'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('deer'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('diabetes'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('djinn'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('education'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('eland'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('elk'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('emoji'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('equipment'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('evidence'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('faroese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('feedback'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('fish'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('flounder'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('flour'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('foochowese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('food'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('furniture'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('gallows'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('genevese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('genoese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('gilbertese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('gold'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('headquarters'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('herpes'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('hijinks'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('homework'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('hottentotese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('impatience'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('information'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('innings'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('jackanapes'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('jeans'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('jedi'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('kiplingese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('knowledge'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('kongoese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('leather'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('love'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('lucchese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('luggage'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('mackerel'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('Maltese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('management'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('metadata'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('mews'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('money'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('moose'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('mumps'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('music'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('nankingese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('news'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('nexus'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('niasese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('nutrition'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('offspring'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('oil'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('patience'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('pekingese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('piedmontese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('pincers'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('pistoiese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('plankton'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('pliers'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('pokemon'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('police'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('polish'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('portuguese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('proceedings'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('progress'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('rabies'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('rain'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('research'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('rhinoceros'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('rice'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('salmon'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('sand'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('sarawakese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('scissors'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('sea[- ]bass'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('series'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('shavese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('shears'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('sheep'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('siemens'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('silk'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('sms'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('soap'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('social media'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('spam'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('species'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('staff'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('sugar'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('swine'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('talent'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('toothpaste'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('traffic'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('travel'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('trousers'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('trout'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('tuna'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('us'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('vermontese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('vinegar'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('weather'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('wenchowese'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('wheat'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('whiting'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('wildebeest'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('wood'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('wool'));
        (yield new \RectorPrefix20211020\Doctrine\Inflector\Rules\Pattern('yengeese'));
    }
}
