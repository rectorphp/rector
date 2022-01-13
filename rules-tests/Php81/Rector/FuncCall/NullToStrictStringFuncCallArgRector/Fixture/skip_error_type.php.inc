<?php

namespace Rector\Tests\Php80\Rector\FuncCall\NullToStrictStringFuncCallArgRector\Fixture;

use Nette\Utils\Strings;

final class SkipErrorType
{
    public function run($regex)
    {
        Strings::replace('value', $regex, function (array $match) use (
            &$string
        ): string {
            $innerPattern = $match['content'];
            $positionDelimiter = strpos($innerPattern, $this->delimiter);

            if ($positionDelimiter > 0) {
                $innerPattern = str_replace($this->delimiter, '\\' . $this->delimiter, $innerPattern);
            }

            // change delimiter
            if (strlen($innerPattern) > 2 && $innerPattern[0] === $innerPattern[strlen($innerPattern) - 1]) {
                return 'foo';
            }
        });
    }
}
