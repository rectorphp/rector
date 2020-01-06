<?php

declare(strict_types=1);

namespace Rector\Polyfill\FeatureSupport;

use Rector\Php\PhpVersionProvider;

final class FunctionSupportResolver
{
    /**
     * @var string[][]
     */
    private const FUNCTIONS_BY_VERSION = [
        '5.6' => ['session_abort'],
    ];

    /**
     * @var PhpVersionProvider
     */
    private $phpVersionProvider;

    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }

    public function isFunctionSupported(string $desiredFunction): bool
    {
        foreach (self::FUNCTIONS_BY_VERSION as $version => $functions) {
            foreach ($functions as $function) {
                if ($desiredFunction !== $function) {
                    continue;
                }

                if (! $this->phpVersionProvider->isAtLeast($version)) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }
}
