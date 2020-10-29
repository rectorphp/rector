<?php

declare(strict_types=1);

namespace Rector\Core\Skip;

use Nette\Utils\Strings;
use Rector\Core\Configuration\Option;
use Rector\Core\Rector\AbstractRector;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileInfo;

final class Skipper
{
    /**
     * @var string
     * @see https://regex101.com/r/s7Rv0c/1
     */
    private const ONLY_ENDS_WITH_ASTERISK_REGEX = '#^[^*](.*?)\*$#';

    /**
     * @var string
     * @see https://regex101.com/r/I2z414/1
     */
    private const ONLY_STARTS_WITH_ASTERISK_REGEX = '#^\*(.*?)[^*]$#';

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    public function __construct(ParameterProvider $parameterProvider)
    {
        $this->parameterProvider = $parameterProvider;
    }

    public function shouldSkipFileInfoAndRule(SmartFileInfo $smartFileInfo, AbstractRector $rector): bool
    {
        $skip = $this->parameterProvider->provideArrayParameter(Option::SKIP);
        if ($skip === []) {
            return false;
        }

        $rectorClass = get_class($rector);
        if (! array_key_exists($rectorClass, $skip)) {
            return false;
        }

        $locations = $skip[$rectorClass];
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
        if (Strings::match($path, self::ONLY_ENDS_WITH_ASTERISK_REGEX)) {
            return '*' . $path;
        }

        // starts with *
        if (Strings::match($path, self::ONLY_STARTS_WITH_ASTERISK_REGEX)) {
            return $path . '*';
        }

        return $path;
    }
}
