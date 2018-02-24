<?php declare(strict_types=1);

class SomeVisitor implements \PhpParser\NodeVisitor
{
    public function leaveNode(\PhpParser\Node $node)
    {
        return \PhpParser\NodeTraverser::REMOVE_NODE;
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}