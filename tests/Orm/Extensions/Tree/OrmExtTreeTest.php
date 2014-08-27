<?php

require_once realpath(__DIR__ . '/../../../../framework/boot.php');

class mTestMigration extends T4\Orm\Migration
{
    public function up()
    {
        $this->createTable('comments',
            [
                'author' => ['type' => 'string'],
                'text' => ['type' => 'text'],
            ],
            [],
            ['tree']
        );
    }

    public function down()
    {
        $this->dropTable('comments');
    }
}

class CommentTestModel extends T4\Orm\Model
{
    static protected $schema = [
        'table' => 'comments',
        'columns' => [
            'author' => ['type' => 'string'],
            'text' => ['type' => 'text'],
        ]
    ];
    static protected $extensions = ['tree'];
}

class Test extends PHPUnit_Extensions_Database_TestCase

{
    protected $connection;

    public function __construct()
    {
        $config = $this->getT4ConnectionConfig();
        $this->connection = new \Pdo('mysql:dbname=' . $config->dbname . ';host=' . $config->host . '', $config->user, $config->password);
        $this->connection->query('DROP TABLE `comments`');

        $migration = new mTestMigration();
        $migration->setDb($this->getT4Connection());
        $migration->up();
    }

    protected function getT4ConnectionConfig()
    {
        return new \T4\Core\Std(['driver' => 'mysql', 'host' => 'localhost', 'dbname' => 't4test', 'user' => 'root', 'password' => '']);
    }

    protected function getT4Connection()
    {
        return new \T4\Dbal\Connection($this->getT4ConnectionConfig());
    }

    public function testMigrationUp()
    {
        $columns = $this->connection->query('SHOW COLUMNS FROM `comments`')->fetchAll();
        $this->assertCount(7, $columns);
        $this->assertEquals('__id', $columns[0]['Field']);
        $this->assertEquals('author', $columns[1]['Field']);
        $this->assertEquals('text', $columns[2]['Field']);
        $this->assertEquals('__lft', $columns[3]['Field']);
        $this->assertEquals('__rgt', $columns[4]['Field']);
        $this->assertEquals('__lvl', $columns[5]['Field']);
        $this->assertEquals('__prt', $columns[6]['Field']);
    }

    public function testInsert()
    {
        CommentTestModel::setConnection($this->getT4Connection());

        $comment1 = new CommentTestModel();
        $comment1->save();

        $id = $comment1->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(1, $res['__lft']);
        $this->assertEquals(2, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);

        $comment2 = new CommentTestModel();
        $comment2->save();

        $id = $comment2->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(3, $res['__lft']);
        $this->assertEquals(4, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);

        $comment11 = new CommentTestModel();
        $comment11->parent = $comment1;
        $comment11->save();

        $id = $comment1->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(1, $res['__lft']);
        $this->assertEquals(4, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);

        $id = $comment11->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(2, $res['__lft']);
        $this->assertEquals(3, $res['__rgt']);
        $this->assertEquals(1, $res['__lvl']);
        $this->assertEquals($comment1->getPk(), $res['__prt']);

        $id = $comment2->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(5, $res['__lft']);
        $this->assertEquals(6, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);

        $comment111 = $comment2;
        $comment111->parent = $comment11;
        $comment111->save();

        $id = $comment1->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(1, $res['__lft']);
        $this->assertEquals(6, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);

        $id = $comment11->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(2, $res['__lft']);
        $this->assertEquals(5, $res['__rgt']);
        $this->assertEquals(1, $res['__lvl']);
        $this->assertEquals($comment1->getPk(), $res['__prt']);

        $id = $comment111->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(3, $res['__lft']);
        $this->assertEquals(4, $res['__rgt']);
        $this->assertEquals(2, $res['__lvl']);
        $this->assertEquals($comment11->getPk(), $res['__prt']);

        $comment2 = $comment111;
        $comment2->parent = null;
        $comment2->save();

        $id = $comment1->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(1, $res['__lft']);
        $this->assertEquals(4, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);

        $id = $comment11->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(2, $res['__lft']);
        $this->assertEquals(3, $res['__rgt']);
        $this->assertEquals(1, $res['__lvl']);
        $this->assertEquals($comment1->getPk(), $res['__prt']);

        $id = $comment111->getPk();
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
        $this->assertEquals(5, $res['__lft']);
        $this->assertEquals(6, $res['__rgt']);
        $this->assertEquals(0, $res['__lvl']);
        $this->assertEquals(0, $res['__prt']);

    }

    /**
     * Returns the test database connection.
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        return $this->createDefaultDBConnection($this->connection, 'mysql');
    }

    /**
     * Returns the test dataset.
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet(__DIR__ . '/OrmExtTreeTest.data.xml');
    }

}