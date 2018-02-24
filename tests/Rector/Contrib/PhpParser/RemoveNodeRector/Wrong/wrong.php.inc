<?php declare(strict_types=1);

class SomeVisitor extends \PhpParser\NodeVisitorAbstract
{
    public function leaveNode(\PhpParser\Node $node)
    {
        return false;
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}