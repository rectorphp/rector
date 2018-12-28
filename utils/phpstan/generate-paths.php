<?php

// experimental, phpstan 0.10.7
// @todo refactor to classes later, if proven working

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use PhpCsFixer\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;
use Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;

require __DIR__ . '/../../vendor/autoload.php';

$phpstanDependencies = __DIR__ . '/../../phpstan-dependencies.json';
$symfonyStyle = (new SymfonyStyleFactory())->create();

// prepare dependencies.json
if (! file_exists($phpstanDependencies)) {
    $process = Process::fromShellCommandline('vendor/bin/phpstan dump-deps src packages > phpstan-dependencies.json');
    $symfonyStyle->note('Dumping dependencies (will take ~10 s)');
    $process->run();
}

$fileDependenciesJson = Json::decode(FileSystem::read($phpstanDependencies), Json::FORCE_ARRAY);

$changedFiles = resolveChangedFiles();

$filesToCheck = [];
foreach ($changedFiles as $changedFile) {
    $changedFile = getcwd() . '/' . $changedFile;
    if (isset($fileDependenciesJson[$changedFile])) {
        $filesToCheck = array_merge($filesToCheck, $fileDependenciesJson[$changedFile], [$changedFile]);
    }
}

$newFiles = resolveNewFiles();

$filesToCheck = array_merge($filesToCheck, $newFiles);
$filesToCheckString = implode(PHP_EOL, $filesToCheck);

FileSystem::write('phpstan-paths.txt', $filesToCheckString);

$symfonyStyle->success(sprintf('%d paths generated to "phpstan-paths.txt"', count($filesToCheck)));

/**
 * @return string[]
 */
function resolveChangedFiles(): array
{
    $process = Process::fromShellCommandline('git status -s');
    $process->run();

    $gitStatusOutput = $process->getOutput();

    $files = [];
    foreach (Strings::matchAll($gitStatusOutput, '# ([\w\/]+\.php)#m') as $match) {
        $files[] = $match[1];
    }

    return $files;
}

/**
 * @return string[]
 */
function resolveNewFiles(): array
{
    $process = Process::fromShellCommandline('git status -s');
    $process->run();

    $gitStatusOutput = $process->getOutput();

    var_dump($gitStatusOutput);

    $files = [];
    foreach (Strings::matchAll($gitStatusOutput, '#\?\? ([\w\/\.]+)#m') as $match) {
        $directoryOrFile = getcwd() . '/' . $match[1];
        if (file_exists($directoryOrFile) && is_file($directoryOrFile)) {
            $files[] = $directoryOrFile;
        } else {
            $finder = (Finder::create())
                ->files()
                ->in($directoryOrFile)
                ->name('*.php');

            foreach ($finder as $phpFile) {
                /** @var SplFileInfo[] $phpFile */
                $files[] = $phpFile->getRealPath();
            }
        }
    }

    return $files;
}
