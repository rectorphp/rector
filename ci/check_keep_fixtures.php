<?php

declare(strict_types=1);

use Nette\Utils\Strings;
use Rector\Testing\PHPUnit\FixtureSplitter;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;

require __DIR__ . '/../vendor/autoload.php';

$finder = new Symfony\Component\Finder\Finder();
$finder->files();
$finder->name('*.php.inc');
$finder->in(__DIR__ . '/../tests');
$finder->in(__DIR__ . '/../packages/*/tests');

$finderSanitizer = new FinderSanitizer();
$smartFileInfos = $finderSanitizer->sanitize($finder);

$symfonyStyleFactory = new SymfonyStyleFactory();
$symfonyStyle = $symfonyStyleFactory->create();

$errors = [];

/** @var \Symfony\Component\Finder\SplFileInfo $smartFileInfo */
foreach ($smartFileInfos as $smartFileInfo) {
    if (!Strings::match($smartFileInfo->getContents(), FixtureSplitter::SPLIT_LINE)) {
        continue;
    }

    // original → expected
    [$originalContent, $expectedContent] = Strings::split($smartFileInfo->getContents(), FixtureSplitter::SPLIT_LINE);
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
    FixtureSplitter::SPLIT_LINE,
    FixtureSplitter::SPLIT_LINE
));

$symfonyStyle->listing($errors);

exit(ShellCode::ERROR);
