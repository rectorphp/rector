<?php

declare(strict_types=1);

namespace Rector\Polyfill\FeatureSupport;

use Rector\Core\Php\PhpVersionProvider;

final class FunctionSupportResolver
{
    /**
     * @var string[][]
     */
    private const FUNCTIONS_BY_VERSION = [
        '5.6' => ['session_abort', 'hash_equals', 'ldap_escape'],
        '7.0' => ['random_int', 'random_bytes', 'intdiv', 'preg_replace_callback_array', 'error_clear_last'],
        '7.1' => ['is_iterable'],
        '7.2' => ['spl_object_id', 'stream_isatty'],
        '7.3' => ['array_key_first', 'array_key_last', 'hrtime', 'is_countable'],
        '7.4' => ['get_mangled_object_vars', 'mb_str_split', 'password_algos'],
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

                if (! $this->phpVersionProvider->isAtLeastPhpVersion($version)) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }
}
