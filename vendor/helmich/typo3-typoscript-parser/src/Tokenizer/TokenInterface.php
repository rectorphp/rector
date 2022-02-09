<?php

declare (strict_types=1);
namespace RectorPrefix20220209\Helmich\TypoScriptParser\Tokenizer;

interface TokenInterface
{
    const TYPE_WHITESPACE = "WS";
    const TYPE_COMMENT_MULTILINE = "COMMENT_MULTILINE";
    const TYPE_COMMENT_ONELINE = "COMMENT";
    const TYPE_RIGHTVALUE_MULTILINE = "RVALUE_MULTILINE";
    const TYPE_RIGHTVALUE = "RVALUE";
    const TYPE_BRACE_OPEN = "BR_OPEN";
    const TYPE_BRACE_CLOSE = "BR_CLOSE";
    const TYPE_CONDITION = "COND";
    const TYPE_CONDITION_ELSE = "COND_ELSE";
    const TYPE_CONDITION_END = "COND_END";
    const TYPE_OBJECT_IDENTIFIER = "OBJ_IDENT";
    const TYPE_OBJECT_CONSTRUCTOR = "OBJ_CONTRUCT";
    const TYPE_OBJECT_MODIFIER = "OBJ_MODIFIER";
    const TYPE_OPERATOR_ASSIGNMENT = "OP_ASSIGN";
    const TYPE_OPERATOR_MODIFY = "OP_MODIFY";
    const TYPE_OPERATOR_COPY = "OP_COPY";
    const TYPE_OPERATOR_REFERENCE = "OP_REF";
    const TYPE_OPERATOR_DELETE = "OP_DELETE";
    const TYPE_INCLUDE = "INCLUDE";
    const TYPE_INCLUDE_NEW = "INCLUDE_NEW";
    const TYPE_EMPTY_LINE = 'NOP';
    public function getType() : string;
    public function getValue() : string;
    public function getSubMatch(string $name) : ?string;
    public function getLine() : int;
    public function getColumn() : int;
}
