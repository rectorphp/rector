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
        $this->mainNodeTraverser = $mainNodeTraverser;
        $this->mainNodeTraverser->addVisitor($deprecationDetector);

        $this->standaloneTraverseNodeTraverser = $standaloneTraverseNodeTraverser;
        $this->parser = $parser;
    }

    /**
     * @param string[] $directories
     */
    public function scanDirectories(array $directories): void
    {
        $files = $this->findPhpFilesInDirectories($directories);

        foreach ($files as $file) {
            $nodes = $this->parser->parseFile($file->getRealPath());
            $this->standaloneTraverseNodeTraverser->traverse($nodes);
            $this->mainNodeTraverser->traverse($nodes);
        }
    }

    /**
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
