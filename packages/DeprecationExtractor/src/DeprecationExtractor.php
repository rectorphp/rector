<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor;

use PhpParser\NodeTraverser;
use Rector\Contract\Parser\ParserInterface;
use Rector\DeprecationExtractor\Exception\DeprecationExtractorException;
use Rector\DeprecationExtractor\NodeVisitor\DeprecationDetector;
use Rector\FileSystem\PhpFilesFinder;
use Rector\NodeTraverser\NodeTraverserFactory;
use Rector\NodeTraverser\StandaloneTraverseNodeTraverser;
use Throwable;

final class DeprecationExtractor
{
    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var StandaloneTraverseNodeTraverser
     */
    private $standaloneTraverseNodeTraverser;

    /**
     * @var PhpFilesFinder
     */
    private $phpFilesFinder;

    /**
     * @var NodeTraverser
     */
    private $deprecationDetectorNodeTraverser;

    public function __construct(
        ParserInterface $parser,
        DeprecationDetector $deprecationDetector,
        StandaloneTraverseNodeTraverser $standaloneTraverseNodeTraverser,
        PhpFilesFinder $phpFilesFinder,
        NodeTraverserFactory $nodeTraverserFactory
    ) {
        $this->parser = $parser;
        $this->standaloneTraverseNodeTraverser = $standaloneTraverseNodeTraverser;
        $this->deprecationDetectorNodeTraverser = $nodeTraverserFactory->createWithNodeVisitor($deprecationDetector);
        $this->phpFilesFinder = $phpFilesFinder;
    }

    /**
     * @param string[] $source
     */
    public function scanDirectoriesAndFiles(array $source): void
    {
        $files = $this->phpFilesFinder->findInDirectoriesAndFiles($source);

        foreach ($files as $file) {
            try {
                $nodes = $this->parser->parseFile($file->getRealPath());
                // this completes parent & child nodes, types and classses
                $this->standaloneTraverseNodeTraverser->traverse($nodes);

                $this->deprecationDetectorNodeTraverser->traverse($nodes);
            } catch (Throwable $throwable) {
                $message = sprintf(
                    'Extracting deperactions from "%s" file failed.',
                    $file->getRealPath()
                );

                throw new DeprecationExtractorException($message, 0, $throwable);
            }
        }
    }
}
