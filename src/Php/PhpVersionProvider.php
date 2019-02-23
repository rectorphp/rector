<?php declare(strict_types=1);

namespace Rector\Php;

final class PhpVersionProvider
{
    /**
     * @var string|null
     */
    private $phpVersionFeatures;

    public function __construct(?string $phpVersionFeatures)
    {
        $this->phpVersionFeatures = $phpVersionFeatures;
    }

    public function provide(): string
    {
        // for tests
        if (defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__')) {
            return '7.5';
        }

        if ($this->phpVersionFeatures) {
            return $this->phpVersionFeatures;
        }

        return PHP_VERSION;
    }

    public function isAtLeast(string $version): bool
    {
        return version_compare($this->provide(), $version) === 1;
    }
}
