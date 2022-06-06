<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Nette\Latte\Parser;

use RectorPrefix20220606\Nette\Utils\Strings;
use RectorPrefix20220606\Rector\Nette\ValueObject\LatteVariableType;
final class VarTypeParser
{
    /**
     * @var string
     * @see https://regex101.com/r/vYlxWm/1
     */
    private const VAR_TYPE_REGEX = '#{varType (?P<type>.*?) \\$(?P<variable>.*?)}#';
    /**
     * @return LatteVariableType[]
     */
    public function parse(string $content) : array
    {
        $matches = Strings::matchAll($content, self::VAR_TYPE_REGEX);
        $variableTypes = [];
        foreach ($matches as $match) {
            $variableTypes[] = new LatteVariableType($match['variable'], $match['type']);
        }
        return $variableTypes;
    }
}
