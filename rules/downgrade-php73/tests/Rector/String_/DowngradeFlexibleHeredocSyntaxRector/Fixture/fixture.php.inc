<?php

namespace Rector\DowngradePhp73\Tests\Rector\FuncCall\DowngradeFlexibleHeredocSyntaxRector\Fixture;

class FixtureClass
{
    public function run()
    {
        $query = <<<SQL
        SELECT *
        FROM `table`
        WHERE `column` = true;
        SQL;
        $this->setOnClick(<<<SQL
        SELECT *
        FROM `table`
        WHERE `column` = true;
        SQL);
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\FuncCall\DowngradeFlexibleHeredocSyntaxRector\Fixture;

class FixtureClass
{
    public function run()
    {
        $query = <<<SQL
SELECT *
FROM `table`
WHERE `column` = true;
SQL;
        $this->setOnClick(<<<SQL
SELECT *
FROM `table`
WHERE `column` = true;
SQL
);
    }
}

?>
