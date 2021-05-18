<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradeStreamIsattyRector\Fixture;

final class UnionOr
{
    public static function detectColors(): bool
    {
        return (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg')
            && getenv('NO_COLOR') === false // https://no-color.org
            && (getenv('FORCE_COLOR')
                || @stream_isatty(STDOUT) // @ may trigger error 'cannot cast a filtered stream on this system'
                || (defined('PHP_WINDOWS_VERSION_BUILD')
                    && (function_exists('sapi_windows_vt100_support') && sapi_windows_vt100_support(STDOUT))
                    || getenv('ConEmuANSI') === 'ON' // ConEmu
                    || getenv('ANSICON') !== false // ANSICON
                    || getenv('term') === 'xterm' // MSYS
                    || getenv('term') === 'xterm-256color' // MSYS
                )
            );
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradeStreamIsattyRector\Fixture;

final class UnionOr
{
    public static function detectColors(): bool
    {
        $streamIsatty = function ($stream) {
            if (\function_exists('stream_isatty')) {
                return stream_isatty($stream);
            }
            if (!\is_resource($stream)) {
                trigger_error('stream_isatty() expects parameter 1 to be resource, ' . \gettype($stream) . ' given', \E_USER_WARNING);
                return false;
            }
            if ('\\' === \DIRECTORY_SEPARATOR) {
                $stat = @fstat($stream);
                // Check if formatted mode is S_IFCHR
                return $stat ? 020000 === ($stat['mode'] & 0170000) : false;
            }
            return \function_exists('posix_isatty') && @posix_isatty($stream);
        };
        return (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg')
            && getenv('NO_COLOR') === false // https://no-color.org
            && (getenv('FORCE_COLOR')
                || @$streamIsatty(STDOUT) // @ may trigger error 'cannot cast a filtered stream on this system'
                || (defined('PHP_WINDOWS_VERSION_BUILD')
                    && (function_exists('sapi_windows_vt100_support') && sapi_windows_vt100_support(STDOUT))
                    || getenv('ConEmuANSI') === 'ON' // ConEmu
                    || getenv('ANSICON') !== false // ANSICON
                    || getenv('term') === 'xterm' // MSYS
                    || getenv('term') === 'xterm-256color' // MSYS
                )
            );
    }
}

?>
