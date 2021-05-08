<?php

namespace Rector\Tests\DowngradePhp70\Rector\String_\DowngradeGeneratedScalarTypesRector\Fixture;

$code = <<<'EOF'
    public function getParameter($name)
    {
        return $name;
    }
EOF;

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\String_\DowngradeGeneratedScalarTypesRector\Fixture;

$code = <<<'EOF'
public function getParameter($name)
{
    return $name;
}

EOF;

?>
