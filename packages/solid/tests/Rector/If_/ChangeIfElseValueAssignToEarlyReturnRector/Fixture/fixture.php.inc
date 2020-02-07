<?php

namespace Rector\SOLID\Tests\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector\Fixture;

class SomeClass
{
    public function run()
    {
        if ($this->hasDocBlock($tokens, $index)) {
            $docToken = $tokens[$this->getDocBlockIndex($tokens, $index)];
        } else {
            $docToken = null;
        }

        return $docToken;
    }
}

?>
-----
<?php

namespace Rector\SOLID\Tests\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector\Fixture;

class SomeClass
{
    public function run()
    {
        if ($this->hasDocBlock($tokens, $index)) {
            return $tokens[$this->getDocBlockIndex($tokens, $index)];
        }

        return null;
    }
}

?>
