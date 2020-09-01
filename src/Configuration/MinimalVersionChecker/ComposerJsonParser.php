<?php

declare(strict_types=1);

namespace Rector\Core\Configuration\MinimalVersionChecker;

use Nette\Utils\Json;
use Nette\Utils\Strings;

/**
 * @see \Rector\Core\Tests\Configuration\ComposerJsonParserTest
 */
final class ComposerJsonParser
{
    /**
     * @var string
     */
    private $composerJson;

    public function __construct(string $composerJson)
    {
        $this->composerJson = $composerJson;
    }

    public function getPhpVersion(): string
    {
        $composerArray = Json::decode($this->composerJson, Json::FORCE_ARRAY);
        return Strings::trim($composerArray['require']['php'], '~^>=*.');
    }
}
