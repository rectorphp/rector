<?php declare(strict_types=1);

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\Strings;

require __DIR__ . '/../../../vendor/autoload.php';

$composerJson = __DIR__ . '/../../../composer.json';

$composerContent = FileSystem::read($composerJson);
$jsonContent = Json::decode($composerContent, Json::FORCE_ARRAY);

// 1. unset primary here
unset($jsonContent['autoload']['psr-4']['Rector\\']);
unset($jsonContent['autoload-dev']['psr-4']['Rector\\Tests\\']);

// 2. sort by namespaces
ksort($jsonContent['autoload']['psr-4']);
ksort($jsonContent['autoload-dev']['psr-4']);

// 3. make core first
$jsonContent['autoload']['psr-4'] = array_merge(['Rector\\' => 'src'], $jsonContent['autoload']['psr-4']);
$jsonContent['autoload-dev']['psr-4'] = array_merge(
    ['Rector\\Tests\\' => 'tests'],
    $jsonContent['autoload-dev']['psr-4']
);

$newComposerJsonContent = Json::encode($jsonContent, Json::PRETTY) . PHP_EOL;
$newComposerJsonContent = inlineSections($newComposerJsonContent, ['keywords', 'bin']);
$newComposerJsonContent = inlineAuthorSection($newComposerJsonContent);

FileSystem::write($composerJson, $newComposerJsonContent);

echo 'DONE';


// used from: https://github.com/Symplify/Symplify/blob/64e1e07c87b1ec5551df07482d68c5085e76824a/packages/MonorepoBuilder/src/FileSystem/JsonFileManager.php#L70
function inlineSections(string $jsonContent, array $inlineSections): string
{
    foreach ($inlineSections as $inlineSection) {
        $pattern = '#("' . preg_quote($inlineSection, '#') . '": )\[(.*?)\](,)#ms';
        $jsonContent = Strings::replace($jsonContent, $pattern, function (array $match): string {
            $inlined = Strings::replace($match[2], '#\s+#', ' ');
            $inlined = trim($inlined);
            $inlined = '[' . $inlined . ']';

            return $match[1] . $inlined . $match[3];
        });
    }

    return $jsonContent;
}

function inlineAuthorSection(string $jsonContent): string
{
    $authorsPattern = '#("authors":\s+\[)(?<authors>.*?)(\])#s';

    $match = Strings::match($jsonContent, $authorsPattern);

    $authorsContent = $match['authors'];
    $authorsContent = Strings::replace($authorsContent, '#,\s+#', ', ');
    $authorsContent = Strings::replace($authorsContent, '#{\s+#', '{ ');
    $authorsContent = Strings::replace($authorsContent, '#\s+}#', ' }');
    $authorsContent = Strings::replace($authorsContent, '#}, {#', "},\n        {");

    $authorsContent = trim($authorsContent);
    $authorsContent = '        ' . $authorsContent;

    return Strings::replace($jsonContent, $authorsPattern, "$1\n" . $authorsContent . "\n    $3");
}
