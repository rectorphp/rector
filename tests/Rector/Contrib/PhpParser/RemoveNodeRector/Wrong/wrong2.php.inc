<?php declare(strict_types=1);

class SomeVisitor implements \PhpParser\NodeVisitor
{
    public function leaveNode(\PhpParser\Node $node)
    {
        return false;
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}