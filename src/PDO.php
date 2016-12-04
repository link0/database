<?php

namespace Link0\Database;

use Link0\Database\Problem\Authentication\AccessDenied;
use Link0\Database\Problem\Authentication\InvalidPassword;
use Link0\Database\Problem\Authentication\UnknownUser;
use Link0\Database\Problem\Configuration;
use Link0\Database\Problem\TableDoesNotExist;

use PDOException;

final class PDO
{
    private $pdo;

    private $mapping = [
        "42S02" => TableDoesNotExist::class,
        1045    => AccessDenied::class,
    ];

    /**
     * PDO constructor.
     * @param string $dsn
     * @param string $username
     * @param string $passwd
     * @param array $options
     */
    public function __construct($dsn, $username = null, $passwd = null, $options = [])
    {
        try {
            $this->pdo = new \PDO($dsn, $username, $passwd, $options);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch(\PDOException $pdoException) {
            $this->handleException($pdoException);
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     */
    public function __call(string $name, array $arguments)
    {
        try {
            return call_user_func_array([$this->pdo, $name], $arguments);
        } catch(\PDOException $exception) {
            $this->handleException($exception);
        }
    }

    private function handleException(PDOException $pdoException)
    {
        $problem = null;

        if (array_key_exists($pdoException->getCode(), $this->mapping)) {
            $problem = $this->mapping[$pdoException->getCode()];
        }

        if ($problem === null && $pdoException->getCode() === 0) {
           $problem = $this->handleExceptionFromMessage($pdoException);
        }

        // In the case of MySQL, it will sometimes return non-numeric error codes
        // We (unfortunatly) cannot do anything about this. Can't override Problem::getCode() (final method)
        // Even reserializing loses the property on unserialize, since it's not an integer.
        // Let's just say I am disappointed about this little fact.
        // Luckilly we are able to derive type before rethrowing, so it doesn't pose too much of an issue.
        // If you want to retrieve the real code from a Problem, call $problem->getPrevious()->getCode()
        $pdoCode = -1;
        if (is_numeric($pdoException->getCode())) {
            $pdoCode = $pdoException->getCode();
        }

        if (class_exists($problem)) {
            throw new $problem(
                $pdoException->getMessage(),
                $pdoCode,
                $pdoException
            );
        }

        throw $pdoException;
    }

    private function handleExceptionFromMessage(PDOException $pdoException)
    {
        $message = $pdoException->getMessage();

        switch($message) {
            case "invalid data source name":
            case "could not find driver":
                return Configuration::class;
            default:
                return null;
        }
    }
}