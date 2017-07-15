<?php declare(strict_types=1);

namespace Rector\Printer;

use PhpParser\PrettyPrinter\Standard;
use SplFileInfo;

final class CodeStyledPrinter
{
    /**
     * @var Standard
     */
    private $prettyPrinter;

    public function __construct(Standard $prettyPrinter)
    {
        $this->prettyPrinter = $prettyPrinter;
    }

    public function printToFile(SplFileInfo $file, array $originalNodes, array $newNodes): void
    {
        if ($originalNodes === $newNodes) {
            return;
        }

        file_put_contents($file->getRealPath(), $this->printToString($newNodes));
        // @todo: run ecs with minimal set to code look nice
    }

    public function printToString(array $newNodes): string
    {
        return '<?php ' . $this->prettyPrinter->prettyPrint($newNodes);
    }
}
