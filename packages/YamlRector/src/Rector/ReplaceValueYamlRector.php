<?php declare(strict_types=1);

namespace Rector\YamlRector\Rector;

use Nette\Utils\Strings;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\YamlRector\Contract\YamlRectorInterface;
use Rector\YamlRector\PathResolver;

final class ReplaceValueYamlRector implements YamlRectorInterface
{
    /**
     * @var string[]
     */
    private $oldToNewKeyByPaths = [];

    /**
     * @var PathResolver
     */
    private $pathResolver;

    /**
     * @var int
     */
    private $replacePathsCount = 0;

    /**
     * @param mixed[] $oldToNewKeyByPaths
     */
    public function __construct(array $oldToNewKeyByPaths, PathResolver $pathResolver)
    {
        $this->oldToNewKeyByPaths = $oldToNewKeyByPaths;
        $this->pathResolver = $pathResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces specifically nested key value by another.', [
            new CodeSample('key > another_key: old_value', 'key > another_key: old_value'),
        ]);
    }

    public function isCandidate(string $content): bool
    {
        foreach ($this->oldToNewKeyByPaths as $path => $keys) {
            $oldValue = key($keys);

            $pathPattern = $this->createPatternFromPath($path, $oldValue);
            if ((bool) Strings::match($content, $pathPattern)) {
                return true;
            }
        }

        return false;
    }

    public function refactor(string $content): string
    {
        foreach ($this->oldToNewKeyByPaths as $path => $keys) {
            $oldValue = key($keys);

            $pathPattern = $this->createPatternFromPath($path, $oldValue);
            if (Strings::match($content, $pathPattern)) {
                $newValue = current($keys);
                $replacePattern = $this->createReplacePatternFromNewValue($newValue);

                $content = Strings::replace($content, $pathPattern, $replacePattern, 1);
            }
        }

        return $content;
    }

    /**
     * @todo decouple Path helper
     */
    private function createPatternFromPath(string $path, string $value): string
    {
        $this->replacePathsCount = 0;
        $pathParts = $this->pathResolver->splitPathToParts($path);

        $pattern = '';
        foreach ($pathParts as $nesting => $pathPart) {
            $pattern .= sprintf('(%s:)', preg_quote($pathPart));
            ++$this->replacePathsCount;

            // spacing
            if ($nesting === (count($pathParts) - 1)) {
                // last only up-to the key name + the rest
                $pattern .= sprintf('(\s+)(%s)', preg_quote($value));
                $this->replacePathsCount += 2;
            } else {
                $pattern .= '([\n \S+]+)';
                ++$this->replacePathsCount;
            }
        }

        return '#^' . $pattern . '#m';
    }

    private function createReplacePatternFromNewValue(string $value): string
    {
        $replacePattern = '';
        for ($i = 1; $i < $this->replacePathsCount; ++$i) {
            $replacePattern .= '$' . $i;
        }

        return $replacePattern . sprintf("'%s$%d'", $value, $this->replacePathsCount + 1);
    }
}
