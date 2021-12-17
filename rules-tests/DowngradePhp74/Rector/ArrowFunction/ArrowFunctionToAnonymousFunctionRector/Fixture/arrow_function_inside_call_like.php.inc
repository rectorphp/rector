<?php

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class ErrorsConsoleStyle
{

    public function table(int $terminalWidth, int $maxHeaderWidth): void
    {
        $f = static fn ($rows): array => array_map(static fn ($row): array => array_map(static function ($s) use ($terminalWidth, $maxHeaderWidth) {
            if ($terminalWidth > $maxHeaderWidth + 5) {
                return wordwrap(
                    $s,
                    $terminalWidth - $maxHeaderWidth - 5,
                    "\n",
                    true,
                );
            }
            return $s;
        }, $row), $rows);
    }

}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

class ErrorsConsoleStyle
{

    public function table(int $terminalWidth, int $maxHeaderWidth): void
    {
        $f = static function ($rows) use ($terminalWidth, $maxHeaderWidth) : array {
            return array_map(static function ($row) use ($terminalWidth, $maxHeaderWidth) : array {
                return array_map(static function ($s) use ($terminalWidth, $maxHeaderWidth) {
                    if ($terminalWidth > $maxHeaderWidth + 5) {
                        return wordwrap(
                            $s,
                            $terminalWidth - $maxHeaderWidth - 5,
                            "\n",
                            true,
                        );
                    }
                    return $s;
                }, $row);
            }, $rows);
        };
    }

}

?>
