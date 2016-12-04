<?php

/**
 * Class PdoTest
 *
 * Requires a mysql connection on localhost
 * Username: test
 * Password: test
 */
final class PdoTest extends \PHPUnit_Framework_TestCase
{
    private $dsn = 'mysql:host=localhost;dbname=test';

    /**
     * @expectedException \Link0\Database\Problem\Authentication\AccessDenied
     */
    public function test_unkown_user_causes_problem()
    {
        new \Link0\Database\PDO($this->dsn, "does_not_exist");
    }

    /**
     * @expectedException \Link0\Database\Problem\Authentication\AccessDenied
     */
    public function test_valid_user_wrong_password_causes_problem()
    {
        new \Link0\Database\PDO($this->dsn, "test", "invalid_password");
    }

    public function test_valid_user_poses_no_problem()
    {
        $this->pdo();
    }

    /**
     * @expectedException \Link0\Database\Problem\TableDoesNotExist
     */
    public function test_nonexisting_table_poses_problem()
    {
        $this->pdo()->query("SELECT 1 FROM nonexisting");
    }

    private function pdo()
    {
        return new \Link0\Database\PDO($this->dsn, "test", "test");
    }
}
