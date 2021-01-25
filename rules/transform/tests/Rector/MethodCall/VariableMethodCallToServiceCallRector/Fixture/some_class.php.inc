<?php

namespace Rector\Transform\Tests\Rector\MethodCall\VariableMethodCallToServiceCallRector\Fixture;

class SomeClass
{
    public function run(\PhpParser\Node $node)
    {
        $phpDocInfo = $node->getAttribute('php_doc_info');
    }
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\MethodCall\VariableMethodCallToServiceCallRector\Fixture;

class SomeClass
{
    public function __construct(private \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory)
    {
    }
    public function run(\PhpParser\Node $node)
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
    }
}

?>
