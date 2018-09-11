<?php declare(strict_types=1);

namespace Rector\YamlRector\Rector;

use Nette\Utils\Strings;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\YamlRector\Contract\YamlRectorInterface;
use Rector\YamlRector\PathResolver;

final class RenameSubKeyYamlRector implements YamlRectorInterface
{
    /**
     * @var string[]
     */
    private $pathsToNewKeys = [];

    /**
     * @var PathResolver
     */
    private $pathResolver;

    /**
     * @var int
     */
    private $replacePathsCount = 0;

    /**
     * @param string[] $pathsToNewKeys
     */
    public function __construct(array $pathsToNewKeys, PathResolver $pathResolver)
    {
        $this->pathsToNewKeys = $pathsToNewKeys;
        $this->pathResolver = $pathResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces specifically nested key by another.', [
            new CodeSample('key > another_key > old_key: value', 'key > another_key > new_key: value'),
        ]);
    }

    public function refactor(string $content): string
    {
        // @see https://stackoverflow.com/a/32185032/1348344
        // this means: split by newline followed by main key
        $contentMainKeyParts = Strings::split($content, '#\n(?=[\w])#');

        foreach ($contentMainKeyParts as $key => $contentMainKeyPart) {
            $contentMainKeyParts[$key] = $this->processSingleMainGroup($contentMainKeyPart);
        }

        return implode(PHP_EOL, $contentMainKeyParts);
    }

    private function createPatternFromPath(string $path): string
    {
        $pathParts = $this->pathResolver->splitPathToParts($path);

        $this->replacePathsCount = 0;

        $pattern = '';
        foreach ($pathParts as $nesting => $pathPart) {
            $pattern .= sprintf('(%s)', preg_quote($pathPart));
            ++$this->replacePathsCount;

            if ($nesting === (count($pathParts) - 1)) {
                // last only up-to the key name + the rest
                $pattern .= '(.*?)';
                ++$this->replacePathsCount;
            } else {
                $pattern .= '([\n \S+]+)';
                ++$this->replacePathsCount;
            }
        }

        return '#^' . $pattern . '#m';
    }

    private function createReplacePatternFromNewKey(string $newKey): string
    {
        $replacePattern = '';
        for ($i = 1; $i < ($this->replacePathsCount - 1); ++$i) {
            $replacePattern .= '$' . $i;
        }

        return $replacePattern . $newKey . '$' . $this->replacePathsCount;
    }

    private function processSingleMainGroup(string $content): string
    {
        foreach ($this->pathsToNewKeys as $path => $newKey) {
            $pathPattern = $this->createPatternFromPath($path);
            $replacePattern = $this->createReplacePatternFromNewKey($newKey);

            while (Strings::match($content, $pathPattern)) {
                $content = Strings::replace($content, $pathPattern, $replacePattern, 1);
            }
        }

        return $content;
    }
}
