<?php declare(strict_types=1);

namespace Rector\YamlRector\Rector;

use Nette\Utils\Strings;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces one string by another. Use only for very specific strings only.', [
            new CodeSample('key: !super_old_complex', 'key: !super_old_complex'),
        ]);
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
