<?php
namespace tests;

use \Tk\Config;
use \Tk\Db\Pdo;

/**
 * Class PdoTest
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class PdoTest extends \PHPUnit_Framework_TestCase
{
    const TBL = 'ztest';

    /**
     * @var Pdo
     */
    private $db = null;


    public function __construct()
    {
        parent::__construct('Pdo Test');

    }

    public function setUp()
    {
        
        
        
        
        
        $dbcon = Config::getInstance()->get('db.connect.default');
        if (!$dbcon['dbname']) {
            // Stop here and mark this test as incomplete.
            $this->markTestSkipped('No database connection parameters, test skipped....');
            return;
        }

        $this->db = Config::getInstance()->getDb();
        // Create a table add some data
        $sql = file_get_contents(dirname(__FILE__) . '/data/ztest.sql');
        if ($this->db->tableExists(self::TBL)) {
            $sql = sprintf('DROP TABLE `%s`', self::TBL);
            $this->db->query($sql);
        }
        $this->db->multiQuery($sql);
    }

    public function tearDown()
    {
        // Delete table and data....
        if (!$this->db) return;
        
        $sql = sprintf('DROP TABLE `%s`', self::TBL);
        $this->db->query($sql);

    }


    /**
     *
     *
     */
    public function testSelect()
    {
//        $sql = sprintf('SELECT * FROM `ztest` WHERE `id` = 142');
//        $result = $this->db->query($sql);
//        $row = $result->current();
//
//        $this->assertEquals($row['articleId'], 90, 'ID of 90 expected');
//        $this->assertEquals($row['userId'], 51, 'ID of 51 expected');
//        $this->assertTrue($row['publish']);
//
//        $result->setFetchMode(Tk_Db_Pdo::FETCH_OBJ);
//        $row = $result->current();
//
//        $this->assertEquals($row->articleId, 90, 'ID of 90 expected');
//        $this->assertEquals($row->userId, 51, 'ID of 51 expected');
//        $this->assertTrue($row->publish);
    }

    /**
     *
     *
     */
    public function testInsert()
    {
        // Test Insert
//        $sql = sprintf("INSERT INTO `ztest` (`id`, `articleId`, `userId`, `author`, `body`, `image`, `file`, `publish`, `mapLat`, `mapLng`, `mapZoom`, `orderBy`, `modified`, `created`) VALUES
//(NULL, 22, 44, 'test', 'This is a test record!', '', '', 1, 0, 0, 0, 18, NOW(), NOW() )");
//        $result = $this->db->query($sql);
//        $id = $this->db->getInsertId();
//        $this->assertEquals($id, 278, 'ID of 278 expected');

    }

    /**
     *
     *
     */
    public function testUpdate()
    {
        // Test Update
//        $sql = sprintf("UPDATE `ztest` SET `author` = 'anon' WHERE `id` = 277");
//        $result = $this->db->query($sql);
//
//        $sql = sprintf('SELECT * FROM `ztest` WHERE `id` = 277');
//        $result = $this->db->query($sql);
//        $result->setFetchMode(Tk_Db_Pdo::FETCH_OBJ);
//        $row = $result->current();
//        $this->assertEquals($row->author, 'anon', 'String of `anon` expected');
//
//
//        $sql = sprintf('SELECT * FROM `ztest`');
//        $count = $this->db->countQuery($sql);
//        $this->assertEquals($count, 266, 'Int of `266` expected');

    }

    /**
     *
     *
     */
    public function testDelete()
    {
        // Test Delete
//        $sql = sprintf("DELETE FROM `ztest` WHERE `id` = 277");
//        $result = $this->db->query($sql);
//
//        $sql = sprintf('SELECT * FROM `ztest`');
//        $count = $this->db->countQuery($sql);
//        $this->assertEquals($count, 265, 'Int of `265` expected');



    }

}

