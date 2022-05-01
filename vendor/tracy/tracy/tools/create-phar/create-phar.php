<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

// creates tracy.phar
if (!\class_exists('Phar') || \ini_get('phar.readonly')) {
    echo "Enable Phar extension and set directive 'phar.readonly=off'.\n";
    die(1);
}
function compressJs(string $s) : string
{
    if (\function_exists('curl_init')) {
        $curl = \curl_init('https://closure-compiler.appspot.com/compile');
        \curl_setopt($curl, \CURLOPT_RETURNTRANSFER, 1);
        \curl_setopt($curl, \CURLOPT_POST, 1);
        \curl_setopt($curl, \CURLOPT_POSTFIELDS, 'output_info=compiled_code&js_code=' . \urlencode($s));
        $s = \curl_exec($curl) ?: $s;
        \curl_close($curl);
    }
    return $s;
}
function compressCss(string $s) : string
{
    $s = \preg_replace('#/\\*.*?\\*/#s', '', $s);
    // remove comments
    $s = \preg_replace('#[ \\t\\r\\n]+#', ' ', $s);
    // compress space, ignore hard space
    $s = \preg_replace('# ([^0-9a-z.\\#*-])#i', '$1', $s);
    $s = \preg_replace('#([^0-9a-z%)]) #i', '$1', $s);
    $s = \str_replace(';}', '}', $s);
    // remove leading semicolon
    return \trim($s);
}
@\unlink('tracy.phar');
// @ - file may not exist
$phar = new \Phar('tracy.phar');
$phar->setStub("<?php\nrequire 'phar://' . __FILE__ . '/tracy.php';\n__HALT_COMPILER();\n");
$phar->startBuffering();
foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__ . '/../../src', \RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
    echo "adding: {$iterator->getSubPathname()}\n";
    $s = \file_get_contents($file->getPathname());
    if (\strpos($s, '@tracySkipLocation') === \false) {
        $s = \php_strip_whitespace($file->getPathname());
    }
    if ($file->getExtension() === 'js') {
        $s = \RectorPrefix20220501\compressJs($s);
    } elseif ($file->getExtension() === 'css') {
        $s = \RectorPrefix20220501\compressCss($s);
    } elseif ($file->getExtension() === 'phtml') {
        $s = \preg_replace_callback('#(<(script|style).*(?<![?=])>)(.*)(</)#Uis', function ($m) : string {
            [, $begin, $type, $s, $end] = $m;
            if ($s === '' || \strpos($s, '<?') !== \false) {
                return $m[0];
            } elseif ($type === 'script') {
                $s = \RectorPrefix20220501\compressJs($s);
            } elseif ($type === 'style') {
                $s = \RectorPrefix20220501\compressCss($s);
            }
            return $begin . $s . $end;
        }, $s);
    } elseif ($file->getExtension() !== 'php') {
        continue;
    }
    $phar[$iterator->getSubPathname()] = $s;
}
$phar->stopBuffering();
$phar->compressFiles(\Phar::GZ);
echo "OK\n";
