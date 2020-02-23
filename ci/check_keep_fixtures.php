<?php

declare(strict_types=1);

use Nette\Utils\Strings;
use Rector\Core\Testing\PHPUnit\FixtureSplitter;
use Rector\Core\Testing\ValueObject\SplitLine;
use Symfony\Component\Finder\Finder;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;

require __DIR__ . '/../vendor/autoload.php';

$finder = new Finder();
$finder->files();
$finder->name('*.php.inc');
$finder->in(__DIR__ . '/../tests');
$finder->in(__DIR__ . '/../packages/*/tests');
$finder->in(__DIR__ . '/../rules/*/tests');

$finderSanitizer = new FinderSanitizer();
$smartFileInfos = $finderSanitizer->sanitize($finder);

$symfonyStyleFactory = new SymfonyStyleFactory();
$symfonyStyle = $symfonyStyleFactory->create();

$errors = [];

/** @var SmartFileInfo $smartFileInfo */
foreach ($smartFileInfos as $smartFileInfo) {
    if (! Strings::match($smartFileInfo->getContents(), SplitLine::SPLIT_LINE)) {
        continue;
    }

    // original → expected
    [$originalContent, $expectedContent] = Strings::split($smartFileInfo->getContents(), SplitLine::SPLIT_LINE);
    if ($originalContent !== $expectedContent) {
        continue;
    }

    $errors[] = $smartFileInfo->getRelativeFilePathFromCwd();
}

if ($errors === []) {
    exit(ShellCode::SUCCESS);
}

$symfonyStyle->warning(sprintf(
    'These files have same content before "%s" and after it. Remove the content after "%s"',
    SplitLine::SPLIT_LINE,
    SplitLine::SPLIT_LINE
));

$symfonyStyle->listing($errors);

exit(ShellCode::ERROR);
