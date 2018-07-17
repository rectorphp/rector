<?php declare(strict_types=1);

namespace Rector\YamlRector\Rector;

use Nette\Utils\Strings;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\YamlRector\Contract\YamlRectorInterface;

final class RenameSubKeyYamlRector implements YamlRectorInterface
{
    /**
     * @var string[]
     */
    private $pathsToNewKeys = [];

    /**
     * @param string[] $pathsToNewKeys
     */
    public function __construct(array $pathsToNewKeys)
    {
        $this->pathsToNewKeys = $pathsToNewKeys;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces specifically nested key by another.', [
            new CodeSample('key > another_key > old_key: value', 'key > another_key > new_key: value'),
        ]);
    }

    public function isCandidate(string $content): bool
    {
        foreach ($this->pathsToNewKeys as $path => $newKey) {
            $pathPattern = $this->createPatternFromPath($path);

            if ((bool) Strings::match($content, $pathPattern)) {
                return true;
            }
        }

        return false;
    }

    public function refactor(string $content): string
    {
        foreach ($this->pathsToNewKeys as $path => $newKey) {
            $pathPattern = $this->createPatternFromPath($path);

            while (Strings::match($content, $pathPattern)) {
                $replacement = $this->createReplacementFromPathAndNewKey($path, $newKey);
                $content = Strings::replace($content, $pathPattern, $replacement, 1);
            }
        }

        return $content;
    }

    private function createPatternFromPath(string $path): string
    {
        $pathParts = $this->splitPathToParts($path);

        $pattern = '';
        foreach ($pathParts as $nesting => $pathPart) {
            if ($nesting === (count($pathParts) - 1)) {
                // last only up-to the key name + the rest
                $pattern .= sprintf('(%s)(.*?)', preg_quote($pathPart));
            } else {
                $pattern .= sprintf('(%s:)([\n \S+]+)', preg_quote($pathPart));
            }
        }

        return '#^' . $pattern . '#m';
    }

    private function createReplacementFromPathAndNewKey(string $path, string $newKey): string
    {
        $replacement = '';

        $pathParts = $this->splitPathToParts($path);

        $final = 2 * count($pathParts);
        for ($i = 1; $i < $final - 1; ++$i) {
            $replacement .= '$' . $i;
        }

        $replacement .= preg_quote($newKey);
        $replacement .= '$' . ($i + 3);

        return $replacement;
    }

    /**
     * @return string[]
     */
    private function splitPathToParts(string $path): array
    {
        // split by " > " or ">"
        return Strings::split($path, '#[\s+]?>[\s+]?#');
    }
}
