<?php

declare (strict_types=1);
namespace Rector\DeadCode\FeatureSupport;

use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersion;
final class FunctionSupportResolver
{
    /**
     * @var array<int, string[]>
     */
    private const FUNCTIONS_BY_VERSION = [\Rector\Core\ValueObject\PhpVersion::PHP_56 => ['session_abort', 'hash_equals', 'ldap_escape'], \Rector\Core\ValueObject\PhpVersion::PHP_70 => ['random_int', 'random_bytes', 'intdiv', 'preg_replace_callback_array', 'error_clear_last'], \Rector\Core\ValueObject\PhpVersion::PHP_71 => ['is_iterable'], \Rector\Core\ValueObject\PhpVersion::PHP_72 => ['spl_object_id', 'stream_isatty'], \Rector\Core\ValueObject\PhpVersion::PHP_73 => ['array_key_first', 'array_key_last', 'hrtime', 'is_countable'], \Rector\Core\ValueObject\PhpVersion::PHP_74 => ['get_mangled_object_vars', 'mb_str_split', 'password_algos']];
    /**
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function isFunctionSupported(string $desiredFunction) : bool
    {
        foreach (self::FUNCTIONS_BY_VERSION as $version => $functions) {
            if (!\in_array($desiredFunction, $functions, \true)) {
                continue;
            }
            if (!$this->phpVersionProvider->isAtLeastPhpVersion($version)) {
                continue;
            }
            return \true;
        }
        return \false;
    }
}
