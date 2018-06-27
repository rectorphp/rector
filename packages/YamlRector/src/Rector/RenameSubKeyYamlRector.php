<?php declare(strict_types=1);

namespace Rector\YamlRector\Rector;

use Nette\Utils\Strings;
use Rector\YamlRector\Contract\YamlRectorInterface;

final class RenameSubKeyYamlRector implements YamlRectorInterface
{
    /**
     * @var string
     */
    private $mainKey;

    /**
     * @var string
     */
    private $subKey;

    /**
     * @var string
     */
    private $newKey;

    public function __construct(string $mainKey, string $subKey, string $newKey)
    {
        $this->mainKey = $mainKey;
        $this->subKey = $subKey;
        $this->newKey = $newKey;
    }

    public function isCandidate(string $content): bool
    {
        return (bool) Strings::match($content, $this->createPattern());
    }

    public function refactor(string $content): string
    {
        return Strings::replace($content, $this->createPattern(), '$1' . $this->newKey . '$3');
    }

    private function createPattern(): string
    {
        return sprintf('#(^%s:\s+)(%s)(:)#s', preg_quote($this->mainKey), preg_quote($this->subKey));
    }
}
