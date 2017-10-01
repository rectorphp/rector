<?php declare(strict_types=1);

namespace Rector\Regex;

use Nette\Utils\Strings;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionProperty;

final class MagicMethodMatcher
{
    /**
     * @var string
     */
    private const MAGIC_METHODS_PATTERN = '~^
        [ \t*]*  @method  [ \t]+
        (?: [^\s(]+  [ \t]+ )?
        (set|get|is|add)  ([A-Z]\w*)
        (?: ([ \t]* \()  [ \t]* ([^)$\s]*)  )?
    ()~mx';

    /**
     * Mimics https://github.com/nette/utils/blob/v2.3/src/Utils/ObjectMixin.php#L285
     *
     * @return mixed[]
     */
    public function matchInContent(ReflectionClass $classReflection, string $currentNamespace, string $text): array
    {
        $matches = Strings::matchAll($text, self::MAGIC_METHODS_PATTERN, PREG_SET_ORDER);

        $methods = [];

        foreach ($matches as $match) {
            [$all, $op, $prop, $bracket, $type] = $match;

            $name = $op . $prop;
            $prop = strtolower($prop[0]) . substr($prop, 1) . ($op === 'add' ? 's' : '');

            if (! $classReflection->hasProperty($prop)) {
                continue;
            }

            /** @var ReflectionProperty $propertyReflection */
            $propertyReflection = $classReflection->getProperty($prop);

            if ($propertyReflection === null || $propertyReflection->isStatic()) {
                continue;
            }

            $type = $this->resolveType($currentNamespace, $op, $type, $propertyReflection, $match);

            $methods[$name] = [
                'propertyType' => $type,
                'propertyName' => $prop,
                'operation' => $op,
            ];
        }

        return $methods;
    }

    /**
     * @param mixed[] $match
     */
    private function resolveType(
        string $currentNamespace,
        string $op,
        string $type,
        ReflectionProperty $propertyReflection,
        array $match
    ): ?string {
        if ($op === 'get' || $op === 'is') {
            $type = null;
            $op = 'get';
        } elseif (! $type && preg_match(
            '#@var[ \t]+(\S+)' . ($op === 'add' ? '\[\]#' : '#'),
            $propertyReflection->getDocComment(),
            $match
        )) {
            $type = $match[1];
        }

        if ($type && $currentNamespace && preg_match('#^[A-Z]\w+(\[|\||\z)#', $type)) {
            $type = $currentNamespace . '\\' . $type;
        }

        return $type;
    }
}
