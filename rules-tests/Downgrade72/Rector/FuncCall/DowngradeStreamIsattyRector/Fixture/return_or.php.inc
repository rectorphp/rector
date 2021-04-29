<?php

namespace Rector\Tests\Downgrade72\Rector\FuncCall\DowngradeStreamIsattyRector\Fixture;

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

namespace Rector\Tests\Downgrade72\Rector\FuncCall\DowngradeStreamIsattyRector\Fixture;

final class UnionOr
{
    public static function detectColors(): bool
    {
        $streamIsatty = function ($stream) {
            if ('\\' === \DIRECTORY_SEPARATOR) {
                $stat = @fstat($stream);
                return $stat ? 020000 === ($stat['mode'] & 0170000) : false;
            }
            return @posix_isatty($stream);
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
