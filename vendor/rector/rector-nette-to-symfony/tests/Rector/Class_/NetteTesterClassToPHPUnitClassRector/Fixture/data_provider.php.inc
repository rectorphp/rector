<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Fixture;

use Tester\Assert;
use Tester\TestCase;

class DataProviderTest extends TestCase
{
    /**
     * @dataProvider dataMySqlExceptions
     *
     * @param \Exception $exception
     * @param string $class
     * @param array $props
     */
    public function testDriverExceptions_MySQL($exception, $class, array $props)
    {
        $conn = new ConnectionMock([], new MysqlDriverMock());
        $conn->setDatabasePlatform(new Doctrine\DBAL\Platforms\MySqlPlatform());
        $conn->throwOldKdybyExceptions = TRUE;
        $resolved = $conn->resolveException($exception);
        Assert::true($resolved instanceof $class);
        foreach ($props as $prop => $val) {
            Assert::same($val, $resolved->{$prop});
        }
    }
    /**
     * @return array
     */
    public function dataMySqlExceptions()
    {
        $e = new \PDOException('SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'name\' cannot be null', '23000');
        $e->errorInfo = ['23000', 1048, 'Column \'name\' cannot be null'];
        $emptyPdo = new PDOException($e);
        $driver = new MysqlDriverMock();
        $empty = Doctrine\DBAL\DBALException::driverExceptionDuringQuery(
            $driver, $emptyPdo, "INSERT INTO `test_empty` (`name`) VALUES (NULL)", []
        );
        $e = new \PDOException('SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry \'filip-prochazka\' for key \'uniq_name_surname\'', '23000');
        $e->errorInfo = ['23000', 1062, 'Duplicate entry \'filip-prochazka\' for key \'uniq_name_surname\''];
        $uniquePdo = new PDOException($e);
        $unique = Doctrine\DBAL\DBALException::driverExceptionDuringQuery(
            $driver, $uniquePdo, "INSERT INTO `test_empty` (`name`, `surname`) VALUES ('filip', 'prochazka')", []
        );
        return [
            [$empty, \Kdyby\Doctrine\EmptyValueException::class, ['column' => 'name']],
            [$unique, \Kdyby\Doctrine\DuplicateEntryException::class, ['columns' => ['uniq_name_surname' => ['name', 'surname']]]],
        ];
    }
}

?>
-----
<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Fixture;

use Tester\Assert;
use Tester\TestCase;

class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dataMySqlExceptions
     *
     * @param \Exception $exception
     * @param string $class
     * @param array $props
     */
    public function testDriverExceptions_MySQL($exception, $class, array $props)
    {
        $conn = new ConnectionMock([], new MysqlDriverMock());
        $conn->setDatabasePlatform(new Doctrine\DBAL\Platforms\MySqlPlatform());
        $conn->throwOldKdybyExceptions = TRUE;
        $resolved = $conn->resolveException($exception);
        $this->assertTrue($resolved instanceof $class);
        foreach ($props as $prop => $val) {
            $this->assertSame($val, $resolved->{$prop});
        }
    }
    /**
     * @return array
     */
    public function dataMySqlExceptions()
    {
        $e = new \PDOException('SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'name\' cannot be null', '23000');
        $e->errorInfo = ['23000', 1048, 'Column \'name\' cannot be null'];
        $emptyPdo = new PDOException($e);
        $driver = new MysqlDriverMock();
        $empty = Doctrine\DBAL\DBALException::driverExceptionDuringQuery(
            $driver, $emptyPdo, "INSERT INTO `test_empty` (`name`) VALUES (NULL)", []
        );
        $e = new \PDOException('SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry \'filip-prochazka\' for key \'uniq_name_surname\'', '23000');
        $e->errorInfo = ['23000', 1062, 'Duplicate entry \'filip-prochazka\' for key \'uniq_name_surname\''];
        $uniquePdo = new PDOException($e);
        $unique = Doctrine\DBAL\DBALException::driverExceptionDuringQuery(
            $driver, $uniquePdo, "INSERT INTO `test_empty` (`name`, `surname`) VALUES ('filip', 'prochazka')", []
        );
        return [
            [$empty, \Kdyby\Doctrine\EmptyValueException::class, ['column' => 'name']],
            [$unique, \Kdyby\Doctrine\DuplicateEntryException::class, ['columns' => ['uniq_name_surname' => ['name', 'surname']]]],
        ];
    }
}

?>
