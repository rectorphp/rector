<?php

namespace Rector\Privatization\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

use Nette\Utils\Strings;

class ReplaceInArgs
{
    public function isRegexPattern(string $name): bool
    {
        if (Strings::length($name) <= 2) {
            return false;
        }

        $firstChar = $name[0];
        $lastChar = $name[strlen($name) - 1];
        if ($firstChar !== $lastChar) {
            return false;
        }

        // this prevents miss matching like "aMethoda"
        $possibleDelimiters = ['#', '~', '/'];

        return in_array($firstChar, $possibleDelimiters, true);
    }
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

use Nette\Utils\Strings;

class ReplaceInArgs
{
    // this prevents miss matching like "aMethoda"
    /**
     * @var string[]
     */
    private const POSSIBLE_DELIMITERS = ['#', '~', '/'];
    public function isRegexPattern(string $name): bool
    {
        if (Strings::length($name) <= 2) {
            return false;
        }

        $firstChar = $name[0];
        $lastChar = $name[strlen($name) - 1];
        if ($firstChar !== $lastChar) {
            return false;
        }

        return in_array($firstChar, self::POSSIBLE_DELIMITERS, true);
    }
}

?>
