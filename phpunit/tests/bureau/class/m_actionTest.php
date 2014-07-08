<?php

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-03-13 at 15:55:58.
 */
class m_actionTest extends AlterncTest {

    /**
     * @var m_action
     */
    protected $object;

    const TEST_FILE = "/tmp/phpunit-actionTest-file";
    const TEST_DIR = "/tmp/phpunit-actionTest-dir";
    const TEST_UID = 999;

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet() {

        $list = array(
            "testPurge" => "actions-purgeable.yml",
            "testGet_action" => "actions-purgeable.yml",
            "testGet_old" => "actions-purgeable.yml",
            "testFinish" => "actions-purgeable.yml",
            "testReset_job" => "actions-began.yml",
            "testGet_job" => "actions-ready.yml",
            "testCancel" => "actions-purgeable.yml",
            "default" => "actions-purgeable.yml"
        );
        if (isset($list[$this->getName()])) {
            $dataset_file = $list[$this->getName()];
        } else {
            $dataset_file = "actions-empty.yml";
        }
        return parent::loadDataSet($dataset_file);
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        parent::setUp();
        $this->object = new m_action;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        global $L_INOTIFY_DO_ACTION;
        parent::tearDown();
        // Removes flag file eventually created 
        if (is_file($L_INOTIFY_DO_ACTION)) {
            unlink($L_INOTIFY_DO_ACTION);
        }
    }

    /**
     * @covers m_action::do_action
     */
    public function testDo_action() {
        global $L_INOTIFY_DO_ACTION;
	file_put_contents("/tmp/log_fser", "hello world from fser");
        $result = $this->object->do_action();
        $this->assertTrue($result);
        $this->assertFileExists($L_INOTIFY_DO_ACTION);
    }

    /**
     * @covers m_action::create_file
     */
    public function testCreate_file() {
        $result = $this->object->create_file(self::TEST_FILE, "phpunit");
        $this->assertTrue($result);
        $this->assertEquals(1, $this->getConnection()->getRowCount('actions'));
    }

    /**
     * @covers m_action::create_dir
     */
    public function testCreate_dir() {
        $result = $this->object->create_dir(self::TEST_FILE);
        $this->assertTrue($result);
        $this->assertEquals(1, $this->getConnection()->getRowCount('actions'));
    }

    /**
     * @covers m_action::fix_user
     */
    public function testFix_user() {
        $result = $this->object->fix_user(self::TEST_UID);
        $this->assertTrue($result);
        $this->assertEquals(1, $this->getConnection()->getRowCount('actions'));
    }

    /**
     * @covers m_action::fix_dir
     */
    public function testFix_dir() {
        $result = $this->object->fix_dir(self::TEST_DIR);
        $this->assertTrue($result);
        $this->assertEquals(1, $this->getConnection()->getRowCount('actions'));
    }

    /**
     * @covers m_action::fix_file
     */
    public function testFix_file() {
        $result = $this->object->fix_file(self::TEST_FILE);
        $this->assertTrue($result);
        $this->assertEquals(1, $this->getConnection()->getRowCount('actions'));
    }

    /**
     * @covers m_action::del
     */
    public function testDel() {
        $result = $this->object->del(self::TEST_DIR);
        $this->assertTrue($result);
        $this->assertEquals(1, $this->getConnection()->getRowCount('actions'));
    }

    /**
     * @covers m_action::move
     */
    public function testMove() {
        $result = $this->object->move(self::TEST_FILE, self::TEST_DIR);
        $this->assertTrue($result);
        $this->assertEquals(1, $this->getConnection()->getRowCount('actions'));
    }

    /**
     * @covers m_action::archive
     */
    public function testArchive() {
        $result = $this->object->archive(self::TEST_DIR);
        $this->assertTrue($result);
        $this->assertEquals(1, $this->getConnection()->getRowCount('actions'));
    }

    /**
     * @covers m_action::set
     */
    public function testSet() {
        // We test only failure, other methods cover success
        $result = $this->object->set(null, null, null);
        $this->assertFalse($result);
        $this->assertEquals(0, $this->getConnection()->getRowCount('actions'));
    }

    /**
     * @covers m_action::get_old
     */
    public function testGet_old() {
        $result = $this->object->get_old();
        $this->assertEquals(1, $result);
    }

    /**
     * @covers m_action::purge
     */
    public function testPurge() {
        $result = $this->object->purge();
        $this->assertEquals(0, $result);
        $expectedTable = $this->loadDataSet("actions-empty.yml")->getTable("actions");
        $currentTable = $this->getConnection()->createQueryTable('actions', 'SELECT * FROM actions');
        $this->assertTablesEqual($expectedTable, $currentTable);
    }

    /**
     * @covers m_action::get_action
     */
    public function testGet_action() {
        $result = $this->object->get_action();
        $this->assertTrue(is_array($result));
        $this->assertCount(1, $result);
        return current($result);
    }

    /**
     * @covers m_action::begin
     * @depends testGet_action
     */
    public function testBegin($action) {
        $result = $this->object->begin($action["id"]);
        $this->assertTrue($result);
    }

    /**
     * @covers m_action::finish
     * @depends testGet_action
     */
    public function testFinish($action) {
        $result = $this->object->finish($action["id"]);
        $this->assertTrue($result);
        $queryTable = $this->getConnection()->createQueryTable(
                'actions', 'SELECT * FROM actions WHERE DAY(end) = DAY(NOW())'
        );
        $row_count = $queryTable->getRowCount();
        $this->assertEquals(1, $row_count);
    }

    /**
     * @covers m_action::reset_job
     * @depends testGet_action
     */
    public function testReset_job($action) {
        $result = $this->object->reset_job($action["id"]);
        $this->assertTrue($result);
        $queryTable = $this->getConnection()->createQueryTable(
                'actions', 'SELECT * FROM actions WHERE end = 0 AND begin = 0  AND status = 0'
        );
        $this->assertEquals(1, $queryTable->getRowCount());
    }

    /**
     * 
     * 
     * @covers m_action::get_job
     */
    public function testGet_job() {
        $result = $this->object->get_job();
        $this->assertTrue(is_array($result));
        $this->assertCount(1, $result);
    }

    /**
     * @covers m_action::cancel
     * @depends testGet_action
     */
    public function testCancel($variable) {
        $result = $this->object->cancel($variable["id"]);
        $this->assertTrue($result);
        $queryTable = $this->getConnection()->createQueryTable(
                'actions', 'SELECT * FROM actions WHERE DAY(end) = DAY(NOW())'
        );
        $row_count = $queryTable->getRowCount();
        $this->assertEquals(1, $row_count);
    }

}
