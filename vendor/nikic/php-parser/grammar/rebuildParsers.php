<?php

namespace RectorPrefix20210511;

$grammarFileToName = [__DIR__ . '/php5.y' => 'Php5', __DIR__ . '/php7.y' => 'Php7'];
$tokensFile = __DIR__ . '/tokens.y';
$tokensTemplate = __DIR__ . '/tokens.template';
$skeletonFile = __DIR__ . '/parser.template';
$tmpGrammarFile = __DIR__ . '/tmp_parser.phpy';
$tmpResultFile = __DIR__ . '/tmp_parser.php';
$resultDir = __DIR__ . '/../lib/PhpParser/Parser';
$tokensResultsFile = $resultDir . '/Tokens.php';
$kmyacc = \getenv('KMYACC');
if (!$kmyacc) {
    // Use phpyacc from dev dependencies by default.
    $kmyacc = __DIR__ . '/../vendor/bin/phpyacc';
}
$options = \array_flip($argv);
$optionDebug = isset($options['--debug']);
$optionKeepTmpGrammar = isset($options['--keep-tmp-grammar']);
///////////////////////////////
/// Utility regex constants ///
///////////////////////////////
const LIB = '(?(DEFINE)
    (?<singleQuotedString>\'[^\\\\\']*+(?:\\\\.[^\\\\\']*+)*+\')
    (?<doubleQuotedString>"[^\\\\"]*+(?:\\\\.[^\\\\"]*+)*+")
    (?<string>(?&singleQuotedString)|(?&doubleQuotedString))
    (?<comment>/\\*[^*]*+(?:\\*(?!/)[^*]*+)*+\\*/)
    (?<code>\\{[^\'"/{}]*+(?:(?:(?&string)|(?&comment)|(?&code)|/)[^\'"/{}]*+)*+})
)';
const PARAMS = '\\[(?<params>[^[\\]]*+(?:\\[(?&params)\\][^[\\]]*+)*+)\\]';
const ARGS = '\\((?<args>[^()]*+(?:\\((?&args)\\)[^()]*+)*+)\\)';
///////////////////
/// Main script ///
///////////////////
$tokens = \file_get_contents($tokensFile);
foreach ($grammarFileToName as $grammarFile => $name) {
    echo "Building temporary {$name} grammar file.\n";
    $grammarCode = \file_get_contents($grammarFile);
    $grammarCode = \str_replace('%tokens', $tokens, $grammarCode);
    $grammarCode = \RectorPrefix20210511\resolveNodes($grammarCode);
    $grammarCode = \RectorPrefix20210511\resolveMacros($grammarCode);
    $grammarCode = \RectorPrefix20210511\resolveStackAccess($grammarCode);
    \file_put_contents($tmpGrammarFile, $grammarCode);
    $additionalArgs = $optionDebug ? '-t -v' : '';
    echo "Building {$name} parser.\n";
    $output = \RectorPrefix20210511\execCmd("{$kmyacc} {$additionalArgs} -m {$skeletonFile} -p {$name} {$tmpGrammarFile}");
    $resultCode = \file_get_contents($tmpResultFile);
    $resultCode = \RectorPrefix20210511\removeTrailingWhitespace($resultCode);
    \RectorPrefix20210511\ensureDirExists($resultDir);
    \file_put_contents("{$resultDir}/{$name}.php", $resultCode);
    \unlink($tmpResultFile);
    echo "Building token definition.\n";
    $output = \RectorPrefix20210511\execCmd("{$kmyacc} -m {$tokensTemplate} {$tmpGrammarFile}");
    \rename($tmpResultFile, $tokensResultsFile);
    if (!$optionKeepTmpGrammar) {
        \unlink($tmpGrammarFile);
    }
}
///////////////////////////////
/// Preprocessing functions ///
///////////////////////////////
function resolveNodes($code)
{
    return \preg_replace_callback('~\\b(?<name>[A-Z][a-zA-Z_\\\\]++)\\s*' . \PARAMS . '~', function ($matches) {
        // recurse
        $matches['params'] = \RectorPrefix20210511\resolveNodes($matches['params']);
        $params = \RectorPrefix20210511\magicSplit('(?:' . \PARAMS . '|' . \ARGS . ')(*SKIP)(*FAIL)|,', $matches['params']);
        $paramCode = '';
        foreach ($params as $param) {
            $paramCode .= $param . ', ';
        }
        return 'new ' . $matches['name'] . '(' . $paramCode . 'attributes())';
    }, $code);
}
function resolveMacros($code)
{
    return \preg_replace_callback('~\\b(?<!::|->)(?!array\\()(?<name>[a-z][A-Za-z]++)' . \ARGS . '~', function ($matches) {
        // recurse
        $matches['args'] = \RectorPrefix20210511\resolveMacros($matches['args']);
        $name = $matches['name'];
        $args = \RectorPrefix20210511\magicSplit('(?:' . \PARAMS . '|' . \ARGS . ')(*SKIP)(*FAIL)|,', $matches['args']);
        if ('attributes' === $name) {
            \RectorPrefix20210511\assertArgs(0, $args, $name);
            return '$this->startAttributeStack[#1] + $this->endAttributes';
        }
        if ('stackAttributes' === $name) {
            \RectorPrefix20210511\assertArgs(1, $args, $name);
            return '$this->startAttributeStack[' . $args[0] . ']' . ' + $this->endAttributeStack[' . $args[0] . ']';
        }
        if ('init' === $name) {
            return '$$ = array(' . \implode(', ', $args) . ')';
        }
        if ('push' === $name) {
            \RectorPrefix20210511\assertArgs(2, $args, $name);
            return $args[0] . '[] = ' . $args[1] . '; $$ = ' . $args[0];
        }
        if ('pushNormalizing' === $name) {
            \RectorPrefix20210511\assertArgs(2, $args, $name);
            return 'if (is_array(' . $args[1] . ')) { $$ = array_merge(' . $args[0] . ', ' . $args[1] . '); }' . ' else { ' . $args[0] . '[] = ' . $args[1] . '; $$ = ' . $args[0] . '; }';
        }
        if ('toArray' == $name) {
            \RectorPrefix20210511\assertArgs(1, $args, $name);
            return 'is_array(' . $args[0] . ') ? ' . $args[0] . ' : array(' . $args[0] . ')';
        }
        if ('parseVar' === $name) {
            \RectorPrefix20210511\assertArgs(1, $args, $name);
            return 'substr(' . $args[0] . ', 1)';
        }
        if ('parseEncapsed' === $name) {
            \RectorPrefix20210511\assertArgs(3, $args, $name);
            return 'foreach (' . $args[0] . ' as $s) { if ($s instanceof Node\\Scalar\\EncapsedStringPart) {' . ' $s->value = Node\\Scalar\\String_::parseEscapeSequences($s->value, ' . $args[1] . ', ' . $args[2] . '); } }';
        }
        if ('makeNop' === $name) {
            \RectorPrefix20210511\assertArgs(3, $args, $name);
            return '$startAttributes = ' . $args[1] . ';' . ' if (isset($startAttributes[\'comments\']))' . ' { ' . $args[0] . ' = new Stmt\\Nop($startAttributes + ' . $args[2] . '); }' . ' else { ' . $args[0] . ' = null; }';
        }
        if ('makeZeroLengthNop' == $name) {
            \RectorPrefix20210511\assertArgs(2, $args, $name);
            return '$startAttributes = ' . $args[1] . ';' . ' if (isset($startAttributes[\'comments\']))' . ' { ' . $args[0] . ' = new Stmt\\Nop($this->createCommentNopAttributes($startAttributes[\'comments\'])); }' . ' else { ' . $args[0] . ' = null; }';
        }
        if ('strKind' === $name) {
            \RectorPrefix20210511\assertArgs(1, $args, $name);
            return '(' . $args[0] . '[0] === "\'" || (' . $args[0] . '[1] === "\'" && ' . '(' . $args[0] . '[0] === \'b\' || ' . $args[0] . '[0] === \'B\')) ' . '? Scalar\\String_::KIND_SINGLE_QUOTED : Scalar\\String_::KIND_DOUBLE_QUOTED)';
        }
        if ('prependLeadingComments' === $name) {
            \RectorPrefix20210511\assertArgs(1, $args, $name);
            return '$attrs = $this->startAttributeStack[#1]; $stmts = ' . $args[0] . '; ' . 'if (!empty($attrs[\'comments\'])) {' . '$stmts[0]->setAttribute(\'comments\', ' . 'array_merge($attrs[\'comments\'], $stmts[0]->getAttribute(\'comments\', []))); }';
        }
        return $matches[0];
    }, $code);
}
function assertArgs($num, $args, $name)
{
    if ($num != \count($args)) {
        die('Wrong argument count for ' . $name . '().');
    }
}
function resolveStackAccess($code)
{
    $code = \preg_replace('/\\$\\d+/', '$this->semStack[$0]', $code);
    $code = \preg_replace('/#(\\d+)/', '$$1', $code);
    return $code;
}
function removeTrailingWhitespace($code)
{
    $lines = \explode("\n", $code);
    $lines = \array_map('rtrim', $lines);
    return \implode("\n", $lines);
}
function ensureDirExists($dir)
{
    if (!\is_dir($dir)) {
        \mkdir($dir, 0777, \true);
    }
}
function execCmd($cmd)
{
    $output = \trim(\shell_exec("{$cmd} 2>&1"));
    if ($output !== "") {
        echo "> " . $cmd . "\n";
        echo $output;
    }
    return $output;
}
//////////////////////////////
/// Regex helper functions ///
//////////////////////////////
function regex($regex)
{
    return '~' . \LIB . '(?:' . \str_replace('~', '\\~', $regex) . ')~';
}
function magicSplit($regex, $string)
{
    $pieces = \preg_split(\RectorPrefix20210511\regex('(?:(?&string)|(?&comment)|(?&code))(*SKIP)(*FAIL)|' . $regex), $string);
    foreach ($pieces as &$piece) {
        $piece = \trim($piece);
    }
    if ($pieces === ['']) {
        return [];
    }
    return $pieces;
}
