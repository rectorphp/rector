<?php declare(strict_types=1);

namespace Rector\YamlRector\Rector;

use Nette\Utils\Strings;
use Rector\YamlRector\Contract\YamlRectorInterface;

final class ReplaceStringYamlRector implements YamlRectorInterface
{
    /**
     * @var string[]
     */
    private $oldToNewString = [];

    /**
     * @param string[] $oldToNewString
     */
    public function __construct(array $oldToNewString)
    {
        $this->oldToNewString = $oldToNewString;
    }

    public function isCandidate(string $content): bool
    {
        foreach ($this->oldToNewString as $oldString => $newString) {
            $oldStringPattern = $this->createPatternFromString($oldString);

            if ((bool) Strings::match($content, $oldStringPattern)) {
                return true;
            }
        }

        return false;
    }

    public function refactor(string $content): string
    {
        foreach ($this->oldToNewString as $oldString => $newString) {
            $oldStringPattern = $this->createPatternFromString($oldString);

            $content = Strings::replace($content, $oldStringPattern, $newString);
        }

        return $content;
    }

    private function createPatternFromString(string $string): string
    {
        return '#' . preg_quote($string, '#') . '#';
    }
}
