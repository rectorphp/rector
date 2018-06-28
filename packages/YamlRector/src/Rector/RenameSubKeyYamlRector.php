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
     * @var string|null
     */
    private $activePathPattern;

    /**
     * @var string[]
     */
    private $activePathSteps = [];

    /**
     * @var string|null
     */
    private $activeNewKey;

    /**
     * @param string[] $pathsToNewKeys
     */
    public function __construct(array $pathsToNewKeys)
    {
        $this->pathsToNewKeys = $pathsToNewKeys;
    }

    public function isCandidate(string $content): bool
    {
        $this->activePathPattern = null;
        $this->activePathSteps = [];
        $this->activeNewKey = null;

        foreach ($this->pathsToNewKeys as $path => $newKey) {
            $pathSteps = Strings::split($path, '#[\s+]?>[\s+]?#');
            $pathPattern = $this->createPattern($pathSteps);

            if ((bool) Strings::match($content, $pathPattern)) {
                $this->activePathSteps = $pathSteps;
                $this->activePathPattern = $pathPattern;
                $this->activeNewKey = $newKey;
                return true;
            }
        }

        return false;
    }

    public function refactor(string $content): string
    {
        $replacement = '';
        for ($i = 1; $i < count($this->activePathSteps); ++$i) {
            $replacement .= '$' . $i;
        }

        $replacement .= preg_quote($this->activeNewKey) . ': ';
        $replacement .= '$' . ($i + 1);

        return Strings::replace($content, $this->activePathPattern, $replacement);
    }

    /**
     * @param string[] $pathSteps
     */
    private function createPattern(array $pathSteps): string
    {
        $pattern = '';
        foreach ($pathSteps as $pathStep) {
            $pattern .= sprintf('(%s:\s+)', preg_quote($pathStep));
        }

        return '#^' . $pattern . '#s';
    }
}
