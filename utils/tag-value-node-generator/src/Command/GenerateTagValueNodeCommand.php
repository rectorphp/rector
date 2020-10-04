<?php

declare(strict_types=1);

namespace Rector\Utils\TagValueNodeGenerator\Command;

use Rector\Core\PhpParser\Parser\SimplePhpParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\SmartFileSystem\FileSystemGuard;

final class GenerateTagValueNodeCommand extends Command
{
    /**
     * @var string
     */
    private const ANNOTATION_FILE_PATH = 'annotation_file_path';

    /**
     * @var SimplePhpParser
     */
    private $simplePhpParser;

    /**
     * @var FileSystemGuard
     */
    private $fileSystemGuard;

    public function __construct(SimplePhpParser $simplePhpParser, FileSystemGuard $fileSystemGuard)
    {
        parent::__construct();

        $this->simplePhpParser = $simplePhpParser;
        $this->fileSystemGuard = $fileSystemGuard;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->addArgument(self::ANNOTATION_FILE_PATH, InputArgument::REQUIRED, 'Path to annotation PHP file');
        $this->setDescription('[DEV] Generate "*TagValueNode" from provided annotation file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $annotationFilePath = (string) $input->getArgument(self::ANNOTATION_FILE_PATH);
        $this->fileSystemGuard->ensureFileExists($annotationFilePath, __METHOD__);
        $nodes = $this->simplePhpParser->parseFile($annotationFilePath);

        dump($nodes);
        die;
    }
}
