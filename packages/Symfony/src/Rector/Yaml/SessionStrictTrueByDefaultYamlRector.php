<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Yaml;

use Nette\Utils\Strings;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\YamlRector\Contract\YamlRectorInterface;

final class SessionStrictTrueByDefaultYamlRector implements YamlRectorInterface
{
    /**
     * @var string
     */
    private const USE_STRICT_MODE_PATTERN = '#(^session:)(.*?)(\n +use_strict_mode: true)#s';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('session > use_strict_mode is true by default and can be removed', [
            new CodeSample('session > use_strict_mode: true', 'session:'),
        ]);
    }

    public function isCandidate(string $content): bool
    {
        return (bool) Strings::matchAll($content, self::USE_STRICT_MODE_PATTERN);
    }

    public function refactor(string $content): string
    {
        return Strings::replace($content, self::USE_STRICT_MODE_PATTERN, '$1$2');
    }
}
