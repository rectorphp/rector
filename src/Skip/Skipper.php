<?php

declare(strict_types=1);

namespace Rector\Core\Skip;

use Nette\Utils\Strings;
use Rector\Core\Rector\AbstractRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class Skipper
{
    /**
     * @var string
     */
    private const ONLY_ENDS_WITH_ASTERISK_PATTERN = '#^[^*](.*?)\*$#';

    /**
     * @var string
     */
    private const ONLY_STARTS_WITH_ASTERISK_PATTERN = '#^\*(.*?)[^*]$#';

    /**
     * @var mixed[]
     */
    private $skip = [];

    /**
     * @param mixed[] $skip
     */
    public function __construct(array $skip = [])
    {
        $this->skip = $skip;
    }

    public function shouldSkipFileInfoAndRule(SmartFileInfo $smartFileInfo, AbstractRector $rector): bool
    {
        if ($this->skip === []) {
            return false;
        }

        $rectorClass = get_class($rector);
        if (! array_key_exists($rectorClass, $this->skip)) {
            return false;
        }

        $locations = $this->skip[$rectorClass];
        $filePathName = $smartFileInfo->getPathName();
        if (in_array($filePathName, $locations, true)) {
            return true;
        }

        $fileName = $smartFileInfo->getFileName();
        foreach ($locations as $location) {
            $ignoredPath = $this->normalizeForFnmatch($location);

            if ($smartFileInfo->endsWith($ignoredPath) || $smartFileInfo->doesFnmatch($ignoredPath)) {
                return true;
            }

            if (rtrim($ignoredPath, '\/') . DIRECTORY_SEPARATOR . $fileName === $filePathName) {
                return true;
            }
        }

        return false;
    }

    /**
     * "value*" → "*value*"
     * "*value" → "*value*"
     */
    private function normalizeForFnmatch(string $path): string
    {
        // ends with *
        if (Strings::match($path, self::ONLY_ENDS_WITH_ASTERISK_PATTERN)) {
            return '*' . $path;
        }

        // starts with *
        if (Strings::match($path, self::ONLY_STARTS_WITH_ASTERISK_PATTERN)) {
            return $path . '*';
        }

        return $path;
    }
}
