<?php declare(strict_types=1);

namespace Rector\YamlRector\Rector;

use Nette\Utils\Strings;
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
        $pathParts = Strings::split($path, '#[\s+]?>[\s+]?#');

        $pattern = '';
        foreach ($pathParts as $key => $pathPart) {
            if ($key === (count($pathParts) - 1)) {
                // last only up-to the key name
                $pattern .= sprintf('(%s)(.*?)', preg_quote($pathPart));
            } else {
                // see https://regex101.com/r/n8XPbV/3/ for ([^:]*?)
                $pattern .= sprintf('(%s:\s+)([^:]*?)', preg_quote($pathPart));
            }
        }

        return '#^' . $pattern . '#ms';
    }

    /**
     * @param string[] $pathSteps
     */
    private function createReplacementFromPathAndNewKey(string $path, string $newKey): string
    {
        $replacement = '';

        $pathParts = Strings::split($path, '#[\s+]?>[\s+]?#');

        $final = 2 * count($pathParts);
        for ($i = 1; $i < $final - 1; ++$i) {
            $replacement .= '$' . $i;
        }

        $replacement .= preg_quote($newKey);
        $replacement .= '$' . ($i + 3);

        return $replacement;
    }
}
