<?php declare(strict_types=1);

namespace Rector\TriggerExtractor;

use Rector\Contract\Parser\ParserInterface;
use Rector\NodeTraverser\MainNodeTraverser;
use Rector\NodeTraverser\StandaloneTraverseNodeTraverser;
use Rector\TriggerExtractor\NodeVisitor\DeprecationDetector;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class TriggerExtractor
{
    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var MainNodeTraverser
     */
    private $mainNodeTraverser;

    /**
     * @var StandaloneTraverseNodeTraverser
     */
    private $standaloneTraverseNodeTraverser;

    public function __construct(
        ParserInterface $parser,
        MainNodeTraverser $mainNodeTraverser,
        DeprecationDetector $deprecationDetector,
        StandaloneTraverseNodeTraverser $standaloneTraverseNodeTraverser
    ) {
        $this->parser = $parser;
        $this->standaloneTraverseNodeTraverser = $standaloneTraverseNodeTraverser;

        $this->mainNodeTraverser = $mainNodeTraverser;
        $this->mainNodeTraverser->addVisitor($deprecationDetector);
    }

    /**
     * @param string[] $directories
     */
    public function scanDirectories(array $directories): void
    {
        $files = $this->findPhpFilesInDirectories($directories);

        foreach ($files as $file) {
            $nodes = $this->parser->parseFile($file->getRealPath());
            // this completes parent & child nodes, types and classses
            $this->standaloneTraverseNodeTraverser->traverse($nodes);
            $this->mainNodeTraverser->traverse($nodes);
        }
    }

    /**
     * @todo duplicated method to
     * @see \Rector\Console\Command\ReconstructCommand, extract to class
     *
     * @param string[] $directories
     * @return SplFileInfo[] array
     */
    private function findPhpFilesInDirectories(array $directories): array
    {
        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->in($directories);

        return iterator_to_array($finder->getIterator());
    }
}
