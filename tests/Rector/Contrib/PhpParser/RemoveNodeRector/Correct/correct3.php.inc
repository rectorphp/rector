<?php declare(strict_types=1);

class MyAbstractVisitor extends \PhpParser\NodeVisitorAbstract
{

}

class SomeVisitor extends MyAbstractVisitor
{
    public function leaveNode(\PhpParser\Node $node)
    {
        return \PhpParser\NodeTraverser::REMOVE_NODE;
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}