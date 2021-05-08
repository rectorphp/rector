<?php

namespace Rector\Tests\DowngradePhp70\Rector\String_\DowngradeGeneratedScalarTypesRector\Fixture;

$code = <<<'EOF'
    public function getParameter(string $name)
    {
        return $name;
    }
EOF;

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\String_\DowngradeGeneratedScalarTypesRector\Fixture;

$code = <<<'EOF'
/**
 * @param string $name
 */
public function getParameter($name)
{
    $name = (string) $name;
    return $name;
}

EOF;

?>
