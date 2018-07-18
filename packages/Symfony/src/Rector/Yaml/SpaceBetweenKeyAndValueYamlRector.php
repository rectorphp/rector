<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\Yaml;

use Nette\Utils\Strings;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\YamlRector\Contract\YamlRectorInterface;

final class SpaceBetweenKeyAndValueYamlRector implements YamlRectorInterface
{
    /**
     * @var string
     */
    private const KEY_WITHOUT_SPACE_AFTER_PATTERN = '#(\w+)\:(\/?\w+)#';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Mappings with a colon (:) that is not followed by a whitespace will get one', [
            new CodeSample('key:value', 'key: value'),
        ]);
    }

    public function isCandidate(string $content): bool
    {
        return (bool) Strings::matchAll($content, self::KEY_WITHOUT_SPACE_AFTER_PATTERN);
    }

    public function refactor(string $content): string
    {
        return Strings::replace($content, self::KEY_WITHOUT_SPACE_AFTER_PATTERN, '$1: $2');
    }
}
