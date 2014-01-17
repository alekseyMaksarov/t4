<?php

namespace T4\Orm;


use T4\Core\Std;
use T4\Dbal\DriverFactory;

abstract class Model
    extends Std
{

    use TCrud;

    /**
     * Имя поля первичного ключа
     */
    const PK = '__id';

    /**
     * Схема модели
     * db*: name of DB connection from application config
     * table: table name
     * colums[] : colums
     * - type
     * @var array
     */
    static protected $schema = [];

    /**
     * Имя таблицы в БД, соответствующей данной модели
     * @return string Имя таблицы в БД
     */
    public static function getTableName()
    {
        if (isset(static::$schema['table']))
            return static::$schema['table'];
        else {
            $className = explode('\\', get_called_class());
            return strtolower(array_pop($className)) . 's';
        }
    }

    public static function getDbDriver()
    {
        $dbConnectionName = static::$schema['db'];
        $driver = \T4\MVC\Application::getInstance()->config->db->{$dbConnectionName}->driver;
        return DriverFactory::getDriver($driver);
    }

    public static function getDbConnection()
    {
        $dbConnectionName = static::$schema['db'];
        $connection = \T4\MVC\Application::getInstance()->db[$dbConnectionName];
        return $connection;
    }

    public static function getColumns() {
        return static::$schema['columns'];
    }

}