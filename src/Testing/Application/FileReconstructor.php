<?php declare(strict_types=1);

namespace Rector\Testing\Application;

use PhpParser\Node;
use PhpParser\Parser;
use Rector\Contract\Dispatcher\ReconstructorInterface;
use Rector\Printer\CodeStyledPrinter;
use SplFileInfo;

final class FileReconstructor
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var CodeStyledPrinter
     */
    private $codeStyledPrinter;

    public function __construct(Parser $parser, CodeStyledPrinter $codeStyledPrinter)
    {
        $this->parser = $parser;
        $this->codeStyledPrinter = $codeStyledPrinter;
    }

    public function processFileWithReconstructor(SplFileInfo $file, ReconstructorInterface $reconstructor): string
    {
        $fileContent = file_get_contents($file->getRealPath());

        /** @var Node[] $nodes */
        $nodes = $this->parser->parse($fileContent);

        foreach ($nodes as $node) {
            if ($reconstructor->isCandidate($node)) {
                $reconstructor->reconstruct($node);
            }
        }

        return $this->codeStyledPrinter->printToString($nodes);
    }
}
