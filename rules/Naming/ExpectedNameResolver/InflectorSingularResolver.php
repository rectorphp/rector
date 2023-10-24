<?php

declare (strict_types=1);
namespace Rector\Naming\ExpectedNameResolver;

use RectorPrefix202310\Nette\Utils\Strings;
use Rector\Core\Util\StringUtils;
/**
 * @see \Rector\Core\Tests\Naming\ExpectedNameResolver\InflectorSingularResolverTest
 */
final class InflectorSingularResolver
{
    /**
     * @var array<string, string>
     */
    private const SINGULARIZE_MAP = ['news' => 'new'];
    /**
     * @var string
     * @see https://regex101.com/r/lbQaGC/3
     */
    private const CAMELCASE_REGEX = '#(?<camelcase>([a-z\\d]+|[A-Z\\d]{1,}[a-z\\d]+|_))#';
    /**
     * @var string
     * @see https://regex101.com/r/2aGdkZ/2
     */
    private const BY_MIDDLE_REGEX = '#(?<by>By[A-Z][a-zA-Z]+)#';
    /**
     * @var string
     */
    private const CAMELCASE = 'camelcase';
    public function resolve(string $currentName) : string
    {
        $matchBy = Strings::match($currentName, self::BY_MIDDLE_REGEX);
        if ($matchBy !== null) {
            return Strings::substring($currentName, 0, -\strlen((string) $matchBy['by']));
        }
        $resolvedValue = $this->resolveSingularizeMap($currentName);
        if ($resolvedValue !== null) {
            return $resolvedValue;
        }
        $singularValueVarName = $this->singularizeCamelParts($currentName);
        if (\in_array($singularValueVarName, ['', '_'], \true)) {
            return $currentName;
        }
        $length = \strlen($singularValueVarName);
        if ($length < 40) {
            return $singularValueVarName;
        }
        return $currentName;
    }
    private function resolveSingularizeMap(string $currentName) : ?string
    {
        foreach (self::SINGULARIZE_MAP as $plural => $singular) {
            if ($currentName === $plural) {
                return $singular;
            }
            if (StringUtils::isMatch($currentName, '#' . \ucfirst($plural) . '#')) {
                $resolvedValue = Strings::replace($currentName, '#' . \ucfirst($plural) . '#', \ucfirst($singular));
                return $this->singularizeCamelParts($resolvedValue);
            }
            if (StringUtils::isMatch($currentName, '#' . $plural . '#')) {
                $resolvedValue = Strings::replace($currentName, '#' . $plural . '#', $singular);
                return $this->singularizeCamelParts($resolvedValue);
            }
        }
        return null;
    }
    private function singularizeCamelParts(string $currentName) : string
    {
        $camelCases = Strings::matchAll($currentName, self::CAMELCASE_REGEX);
        $resolvedName = '';
        foreach ($camelCases as $camelCase) {
            $value = $this->singularize($camelCase[self::CAMELCASE]);
            if (\in_array($camelCase[self::CAMELCASE], ['is', 'has'], \true)) {
                $value = $camelCase[self::CAMELCASE];
            }
            $resolvedName .= $value;
        }
        return $resolvedName;
    }
    // see https://gist.github.com/peter-mcconnell/9757549
    private function singularize(string $word) : string
    {
        $singular = ['/(quiz)zes$/i' => '\\1', '/(matr)ices$/i' => '\\1ix', '/(vert|ind)ices$/i' => '\\1ex', '/^(ox)en/i' => '\\1', '/^(axe)s$/i' => '\\1', '/(alias|status|iris|hoax|hero|gas|fax|circus|canvas|atlas)es$/i' => '\\1', '/([octop|vir])i$/i' => '\\1us', '/(cris|ax|test)es$/i' => '\\1is', '/(shoe|grave|glove|foe|dive|database|curve|cookie|cave|cache|avalanche|abuse)s$/i' => '\\1', '/(o)es$/i' => '\\1', '/(bus|lens)es$/i' => '\\1', '/([m|l])ice$/i' => '\\1ouse', '/(x|ch|ss|sh)es$/i' => '\\1', '/(m)ovies$/i' => '\\1ovie', '/(s)eries$/i' => '\\1eries', '/([^aeiouy]|qu)ies$/i' => '\\1y', '/([lr])ves$/i' => '\\1f', '/(tive)s$/i' => '\\1', '/(hive)s$/i' => '\\1', '/([^f])ves$/i' => '\\1fe', '/(^analy)ses$/i' => '\\1sis', '/((a)naly|(b)a|(d)iagno|empha|(p)arenthe|(p)rogno|(s)ynop|(t)he|(oa)|neuro)ses$/i' => '1\\2sis', '/([ti]|memorand|curricul)a$/i' => '\\1um', '/(n)ews$/i' => '\\1ews', '/s$/i' => ''];
        $irregular = ['alumnus' => 'alumni', 'person' => 'people', 'man' => 'men', 'bacillus' => 'bacilli', 'criterion' => 'criteria', 'fungus' => 'fungi', 'foot' => 'feet', 'goose' => 'geese', 'genus' => 'genera', 'hippopotamus' => 'hippopotami', 'child' => 'children', 'code' => 'codes', 'octopus' => 'octopuses', 'olive' => 'olives', 'chateau' => 'chateaux', 'plateau' => 'plateaux', 'niveau' => 'niveaux', 'passerby' => 'passersby', 'save' => 'saves', 'sex' => 'sexes', 'syllabus' => 'syllabi', 'stimulus' => 'stimuli', 'sku' => 'skus', 'sieve' => 'sieves', 'taxon' => 'taxa', 'taxi' => 'taxis', 'tax' => 'taxes', 'tooth' => 'teeth', 'tights' => 'tights', 'Thief' => 'Thieves', 'terminus' => 'termini', 'larva' => 'larvae', 'leaf' => 'leaves', 'loaf' => 'loaves', 'move' => 'moves', 'nucleus' => 'nuclei', 'valve' => 'valves', 'wave' => 'waves', 'zombie' => 'zombies'];
        // keep words ending in $ignore
        $ignore = ['breeches', 'britches', 'cantus', 'chassis', 'corps', 'coreopsis', 'contretemps', 'coitus', 'clothes', 'clippers', 'data', 'diabetes', 'debris', 'equipment', 'gallows', 'hijinks', 'herpes', 'headquarters', 'information', 'rice', 'socialmedia', 'jeans', 'jackanapes', 'nodemedia', 'money', 'mumps', 'mews', 'innings', 'nexus', 'rhinoceros', 'rabies', 'pants', 'police', 'pliers', 'progress', 'proceedings', 'pincers', 'scissors', 'species', 'series', 'status', 'shorts', 'shears', 'fish', 'sheep', 'press', 'sms', 'trousers', 'trivia', 'yengeese'];
        $lower_word = \strtolower($word);
        foreach ($ignore as $ignore_word) {
            if (\substr($lower_word, -1 * \strlen($ignore_word)) === $ignore_word) {
                return $word;
            }
        }
        foreach ($irregular as $singular_word => $plural_word) {
            $arr = Strings::match($word, '/(' . $plural_word . ')$/i');
            if ($arr !== null) {
                return Strings::replace($word, '/(' . $plural_word . ')$/i', \substr($arr[0], 0, 1) . \substr($singular_word, 1));
            }
        }
        foreach ($singular as $rule => $replacement) {
            if (Strings::match($word, $rule) !== null) {
                return Strings::replace($word, $rule, $replacement);
            }
        }
        return $word;
    }
}
