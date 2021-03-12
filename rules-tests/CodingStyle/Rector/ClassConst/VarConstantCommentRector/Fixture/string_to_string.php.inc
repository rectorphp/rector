<?php

namespace Rector\Tests\CodingStyle\Rector\ClassConst\VarConstantCommentRector\Fixture;

final class StringToString
{
    private const OLD_TO_NEW_TYPE = [
        'CSV' => 'Csv',
        'Excel2003XML' => 'Xml',
        'Excel2007' => 'Xlsx',
        'Excel5' => 'Xls',
    ];
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\ClassConst\VarConstantCommentRector\Fixture;

final class StringToString
{
    /**
     * @var array<string, string>
     */
    private const OLD_TO_NEW_TYPE = [
        'CSV' => 'Csv',
        'Excel2003XML' => 'Xml',
        'Excel2007' => 'Xlsx',
        'Excel5' => 'Xls',
    ];
}

?>
