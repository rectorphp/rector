<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Symplify\EasyTesting\Command;

use RectorPrefix20220501\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix20220501\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20220501\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20220501\Symplify\EasyTesting\Finder\FixtureFinder;
use RectorPrefix20220501\Symplify\EasyTesting\MissplacedSkipPrefixResolver;
use RectorPrefix20220501\Symplify\EasyTesting\ValueObject\Option;
use RectorPrefix20220501\Symplify\PackageBuilder\Console\Command\AbstractSymplifyCommand;
final class ValidateFixtureSkipNamingCommand extends \RectorPrefix20220501\Symplify\PackageBuilder\Console\Command\AbstractSymplifyCommand
{
    /**
     * @var \Symplify\EasyTesting\MissplacedSkipPrefixResolver
     */
    private $missplacedSkipPrefixResolver;
    /**
     * @var \Symplify\EasyTesting\Finder\FixtureFinder
     */
    private $fixtureFinder;
    public function __construct(\RectorPrefix20220501\Symplify\EasyTesting\MissplacedSkipPrefixResolver $missplacedSkipPrefixResolver, \RectorPrefix20220501\Symplify\EasyTesting\Finder\FixtureFinder $fixtureFinder)
    {
        $this->missplacedSkipPrefixResolver = $missplacedSkipPrefixResolver;
        $this->fixtureFinder = $fixtureFinder;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setName('validate-fixture-skip-naming');
        $this->addArgument(\RectorPrefix20220501\Symplify\EasyTesting\ValueObject\Option::SOURCE, \RectorPrefix20220501\Symfony\Component\Console\Input\InputArgument::REQUIRED | \RectorPrefix20220501\Symfony\Component\Console\Input\InputArgument::IS_ARRAY, 'Paths to analyse');
        $this->setDescription('Check that skipped fixture files (without `-----` separator) have a "skip" prefix');
    }
    protected function execute(\RectorPrefix20220501\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20220501\Symfony\Component\Console\Output\OutputInterface $output) : int
    {
        $source = (array) $input->getArgument(\RectorPrefix20220501\Symplify\EasyTesting\ValueObject\Option::SOURCE);
        $fixtureFileInfos = $this->fixtureFinder->find($source);
        $incorrectAndMissingSkips = $this->missplacedSkipPrefixResolver->resolve($fixtureFileInfos);
        foreach ($incorrectAndMissingSkips->getIncorrectSkipFileInfos() as $incorrectSkipFileInfo) {
            $errorMessage = \sprintf('The file "%s" should drop the "skip/keep" prefix', $incorrectSkipFileInfo->getRelativeFilePathFromCwd());
            $this->symfonyStyle->note($errorMessage);
        }
        foreach ($incorrectAndMissingSkips->getMissingSkipFileInfos() as $missingSkipFileInfo) {
            $errorMessage = \sprintf('The file "%s" should start with "skip/keep" prefix', $missingSkipFileInfo->getRelativeFilePathFromCwd());
            $this->symfonyStyle->note($errorMessage);
        }
        $countError = $incorrectAndMissingSkips->getFileCount();
        if ($incorrectAndMissingSkips->getFileCount() === 0) {
            $message = \sprintf('All %d fixture files have valid names', \count($fixtureFileInfos));
            $this->symfonyStyle->success($message);
            return self::SUCCESS;
        }
        $errorMessage = \sprintf('Found %d test file fixtures with wrong prefix', $countError);
        $this->symfonyStyle->error($errorMessage);
        return self::FAILURE;
    }
}
