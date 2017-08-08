<?php declare(strict_types=1);

namespace Rector\Printer;

use PhpParser\PrettyPrinter\Standard;
use SplFileInfo;

final class FormatPerservingPrinter
{
    /**
     * @var Standard
     */
    private $prettyPrinter;

    public function __construct(Standard $prettyPrinter)
    {
        $this->prettyPrinter = $prettyPrinter;
    }

    public function printToFile(SplFileInfo $file, array $newStmts, array $oldStmts, array $oldTokens): void
    {
        if ($oldStmts === $newStmts) {
            return;
        }

        file_put_contents($file->getRealPath(), $this->printToString($newStmts, $oldStmts, $oldTokens));
        // @todo: run ecs with minimal set to code look nice
    }

    public function printToString(array $newStmts, array $oldStmts, array $oldTokens): string
    {
        return $this->prettyPrinter->printFormatPreserving($newStmts, $oldStmts, $oldTokens);
    }
}
