<?php

declare (strict_types=1);
namespace RectorPrefix20210514\Symplify\EasyTesting\Command;

use RectorPrefix20210514\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix20210514\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210514\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20210514\Symplify\EasyTesting\Finder\FixtureFinder;
use RectorPrefix20210514\Symplify\EasyTesting\MissplacedSkipPrefixResolver;
use RectorPrefix20210514\Symplify\EasyTesting\ValueObject\Option;
use RectorPrefix20210514\Symplify\PackageBuilder\Console\Command\AbstractSymplifyCommand;
use RectorPrefix20210514\Symplify\PackageBuilder\Console\ShellCode;
final class ValidateFixtureSkipNamingCommand extends \RectorPrefix20210514\Symplify\PackageBuilder\Console\Command\AbstractSymplifyCommand
{
    /**
     * @var MissplacedSkipPrefixResolver
     */
    private $missplacedSkipPrefixResolver;
    /**
     * @var FixtureFinder
     */
    private $fixtureFinder;
    public function __construct(\RectorPrefix20210514\Symplify\EasyTesting\MissplacedSkipPrefixResolver $missplacedSkipPrefixResolver, \RectorPrefix20210514\Symplify\EasyTesting\Finder\FixtureFinder $fixtureFinder)
    {
        $this->missplacedSkipPrefixResolver = $missplacedSkipPrefixResolver;
        $this->fixtureFinder = $fixtureFinder;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->addArgument(\RectorPrefix20210514\Symplify\EasyTesting\ValueObject\Option::SOURCE, \RectorPrefix20210514\Symfony\Component\Console\Input\InputArgument::REQUIRED | \RectorPrefix20210514\Symfony\Component\Console\Input\InputArgument::IS_ARRAY, 'Paths to analyse');
        $this->setDescription('Check that skipped fixture files (without `-----` separator) have a "skip" prefix');
    }
    protected function execute(\RectorPrefix20210514\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210514\Symfony\Component\Console\Output\OutputInterface $output) : int
    {
        $source = (array) $input->getArgument(\RectorPrefix20210514\Symplify\EasyTesting\ValueObject\Option::SOURCE);
        $fixtureFileInfos = $this->fixtureFinder->find($source);
        $missplacedFixtureFileInfos = $this->missplacedSkipPrefixResolver->resolve($fixtureFileInfos);
        if ($missplacedFixtureFileInfos === []) {
            $message = \sprintf('All %d fixture files have valid names', \count($fixtureFileInfos));
            $this->symfonyStyle->success($message);
            return \RectorPrefix20210514\Symplify\PackageBuilder\Console\ShellCode::SUCCESS;
        }
        foreach ($missplacedFixtureFileInfos['incorrect_skips'] as $missplacedFixtureFileInfo) {
            $errorMessage = \sprintf('The file "%s" should drop the "skip/keep" prefix', $missplacedFixtureFileInfo->getRelativeFilePathFromCwd());
            $this->symfonyStyle->note($errorMessage);
        }
        foreach ($missplacedFixtureFileInfos['missing_skips'] as $missplacedFixtureFileInfo) {
            $errorMessage = \sprintf('The file "%s" should start with "skip/keep" prefix', $missplacedFixtureFileInfo->getRelativeFilePathFromCwd());
            $this->symfonyStyle->note($errorMessage);
        }
        $countError = \count($missplacedFixtureFileInfos['incorrect_skips']) + \count($missplacedFixtureFileInfos['missing_skips']);
        if ($countError === 0) {
            $message = \sprintf('All %d fixture files have valid names', \count($fixtureFileInfos));
            $this->symfonyStyle->success($message);
            return \RectorPrefix20210514\Symplify\PackageBuilder\Console\ShellCode::SUCCESS;
        }
        $errorMessage = \sprintf('Found %d test file fixtures with wrong prefix', $countError);
        $this->symfonyStyle->error($errorMessage);
        return \RectorPrefix20210514\Symplify\PackageBuilder\Console\ShellCode::ERROR;
    }
}
