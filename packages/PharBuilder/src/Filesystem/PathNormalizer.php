<?php declare(strict_types=1);

namespace Rector\PharBuilder\Filesystem;

final class PathNormalizer
{
    /**
     * @var string
     */
    private $pharName;

    public function __construct(string $pharName)
    {
        $this->pharName = $pharName;
    }

    public function normalizeAbsoluteToPharInContent(string $content): string
    {
        return preg_replace(
            "#__DIR__\\s*\\.\\s*'\\/\\.\\.\\/#",
            sprintf("'phar://%s/", $this->pharName),
            $content
        );
    }
}
