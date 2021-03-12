<?php

namespace Rector\DowngradePhp73\Tests\Rector\String_\DowngradeFlexibleHeredocSyntaxRector\Fixture;

class Fixture
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
        $this->setOnClick(<<<JAVASCRIPT
        document.getElementById('{$this->getHtmlId()}').value = '';
        document.getElementById('{$this->getHtmlId()}').onchange();
        JAVASCRIPT);
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp73\Tests\Rector\String_\DowngradeFlexibleHeredocSyntaxRector\Fixture;

class Fixture
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
        $this->setOnClick(<<<JAVASCRIPT
document.getElementById('{$this->getHtmlId()}').value = '';
document.getElementById('{$this->getHtmlId()}').onchange();
JAVASCRIPT
);
    }
}

?>
