<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

final class SkipUnionFunctionParameter
{
    private stdClass $value;
    
    public function setValue(stdClass|string $value): self {
        $this->value = $value;
        
        return $this;
    }
}

?>
