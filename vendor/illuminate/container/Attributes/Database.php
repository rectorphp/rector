<?php

namespace RectorPrefix202601\Illuminate\Container\Attributes;

use Attribute;
use RectorPrefix202601\Illuminate\Contracts\Container\Container;
use RectorPrefix202601\Illuminate\Contracts\Container\ContextualAttribute;
use UnitEnum;
#[Attribute(Attribute::TARGET_PARAMETER)]
class Database implements ContextualAttribute
{
    /**
     * @var \UnitEnum|string|null
     */
    public $connection = null;
    /**
     * Create a new class instance.
     * @param \UnitEnum|string|null $connection
     */
    public function __construct($connection = null)
    {
        $this->connection = $connection;
    }
    /**
     * Resolve the database connection.
     *
     * @param  self  $attribute
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return \Illuminate\Database\Connection
     */
    public static function resolve(self $attribute, Container $container)
    {
        return $container->make('db')->connection($attribute->connection);
    }
}
