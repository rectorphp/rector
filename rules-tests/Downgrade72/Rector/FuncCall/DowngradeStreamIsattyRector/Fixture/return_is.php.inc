<?php

namespace Rector\Tests\Downgrade72\Rector\FuncCall\DowngradeStreamIsattyRector\Fixture;

class SomeClass
{
    public function run($stream)
    {
        return stream_isatty($stream);
    }
}

?>
-----
<?php

namespace Rector\Tests\Downgrade72\Rector\FuncCall\DowngradeStreamIsattyRector\Fixture;

class SomeClass
{
    public function run($stream)
    {
        $streamIsatty = function ($stream) {
            if ('\\' === \DIRECTORY_SEPARATOR) {
                $stat = @fstat($stream);
                return $stat ? 020000 === ($stat['mode'] & 0170000) : false;
            }
            return @posix_isatty($stream);
        };
        return $streamIsatty($stream);
    }
}

?>
