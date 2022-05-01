<?php

namespace RectorPrefix20220501;

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
///////////////////////////////
/// Preprocessing functions ///
///////////////////////////////
function preprocessGrammar($code)
{
    $code = \RectorPrefix20220501\resolveNodes($code);
    $code = \RectorPrefix20220501\resolveMacros($code);
    $code = \RectorPrefix20220501\resolveStackAccess($code);
    return $code;
}
function resolveNodes($code)
{
    return \preg_replace_callback('~\\b(?<name>[A-Z][a-zA-Z_\\\\]++)\\s*' . \PARAMS . '~', function ($matches) {
        // recurse
        $matches['params'] = \RectorPrefix20220501\resolveNodes($matches['params']);
        $params = \RectorPrefix20220501\magicSplit('(?:' . \PARAMS . '|' . \ARGS . ')(*SKIP)(*FAIL)|,', $matches['params']);
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
        $matches['args'] = \RectorPrefix20220501\resolveMacros($matches['args']);
        $name = $matches['name'];
        $args = \RectorPrefix20220501\magicSplit('(?:' . \PARAMS . '|' . \ARGS . ')(*SKIP)(*FAIL)|,', $matches['args']);
        if ('attributes' === $name) {
            \RectorPrefix20220501\assertArgs(0, $args, $name);
            return '$this->startAttributeStack[#1] + $this->endAttributes';
        }
        if ('stackAttributes' === $name) {
            \RectorPrefix20220501\assertArgs(1, $args, $name);
            return '$this->startAttributeStack[' . $args[0] . ']' . ' + $this->endAttributeStack[' . $args[0] . ']';
        }
        if ('init' === $name) {
            return '$$ = array(' . \implode(', ', $args) . ')';
        }
        if ('push' === $name) {
            \RectorPrefix20220501\assertArgs(2, $args, $name);
            return $args[0] . '[] = ' . $args[1] . '; $$ = ' . $args[0];
        }
        if ('pushNormalizing' === $name) {
            \RectorPrefix20220501\assertArgs(2, $args, $name);
            return 'if (is_array(' . $args[1] . ')) { $$ = array_merge(' . $args[0] . ', ' . $args[1] . '); }' . ' else { ' . $args[0] . '[] = ' . $args[1] . '; $$ = ' . $args[0] . '; }';
        }
        if ('toArray' == $name) {
            \RectorPrefix20220501\assertArgs(1, $args, $name);
            return 'is_array(' . $args[0] . ') ? ' . $args[0] . ' : array(' . $args[0] . ')';
        }
        if ('parseVar' === $name) {
            \RectorPrefix20220501\assertArgs(1, $args, $name);
            return 'substr(' . $args[0] . ', 1)';
        }
        if ('parseEncapsed' === $name) {
            \RectorPrefix20220501\assertArgs(3, $args, $name);
            return 'foreach (' . $args[0] . ' as $s) { if ($s instanceof Node\\Scalar\\EncapsedStringPart) {' . ' $s->value = Node\\Scalar\\String_::parseEscapeSequences($s->value, ' . $args[1] . ', ' . $args[2] . '); } }';
        }
        if ('makeNop' === $name) {
            \RectorPrefix20220501\assertArgs(3, $args, $name);
            return '$startAttributes = ' . $args[1] . ';' . ' if (isset($startAttributes[\'comments\']))' . ' { ' . $args[0] . ' = new Stmt\\Nop($startAttributes + ' . $args[2] . '); }' . ' else { ' . $args[0] . ' = null; }';
        }
        if ('makeZeroLengthNop' == $name) {
            \RectorPrefix20220501\assertArgs(2, $args, $name);
            return '$startAttributes = ' . $args[1] . ';' . ' if (isset($startAttributes[\'comments\']))' . ' { ' . $args[0] . ' = new Stmt\\Nop($this->createCommentNopAttributes($startAttributes[\'comments\'])); }' . ' else { ' . $args[0] . ' = null; }';
        }
        if ('strKind' === $name) {
            \RectorPrefix20220501\assertArgs(1, $args, $name);
            return '(' . $args[0] . '[0] === "\'" || (' . $args[0] . '[1] === "\'" && ' . '(' . $args[0] . '[0] === \'b\' || ' . $args[0] . '[0] === \'B\')) ' . '? Scalar\\String_::KIND_SINGLE_QUOTED : Scalar\\String_::KIND_DOUBLE_QUOTED)';
        }
        if ('prependLeadingComments' === $name) {
            \RectorPrefix20220501\assertArgs(1, $args, $name);
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
//////////////////////////////
/// Regex helper functions ///
//////////////////////////////
function regex($regex)
{
    return '~' . \LIB . '(?:' . \str_replace('~', '\\~', $regex) . ')~';
}
function magicSplit($regex, $string)
{
    $pieces = \preg_split(\RectorPrefix20220501\regex('(?:(?&string)|(?&comment)|(?&code))(*SKIP)(*FAIL)|' . $regex), $string);
    foreach ($pieces as &$piece) {
        $piece = \trim($piece);
    }
    if ($pieces === ['']) {
        return [];
    }
    return $pieces;
}
