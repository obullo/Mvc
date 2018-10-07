<?php

namespace Obullo\Logger;

use Psr\Log\LoggerInterface as Logger;
use Doctrine\DBAL\Logging\SQLLogger as SQLLoggerInterface;

/**
 * SQLLogger for Doctrine DBAL
 *
 * @copyright 2018 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class DoctrineSQLLogger implements SQLLoggerInterface
{
    /**
     * Sql
     *
     * @var string
     */
    protected $sql;

    /**
     * Query timer start value
     *
     * @var int
     */
    protected $start;

    /**
     * Logger
     *
     * @var object
     */
    protected $logger;

    /**
     * Bind parameters
     *
     * @var array
     */
    protected $params;

    /**
     * Count number of queries
     *
     * @var integer
     */
    protected $queryNumber = 0;

    /**
     * Create pdo statement object
     *
     * @param \Psr\Log\Logger $logger object
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Begin sql query timer
     *
     * @return void
     */
    protected function beginTimer()
    {
        $this->start = microtime(true);
    }

    /**
     * Logs a SQL statement somewhere.
     *
     * @param string     $sql    The SQL to be executed.
     * @param array|null $params The SQL parameters.
     * @param array|null $types  The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->beginTimer();
        $this->params = $params;
        ++$this->queryNumber;
        $this->sql = $sql;
        $types = null;
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery()
    {
        $this->logger->debug(
            'SQL-'.$this->queryNumber.' ( Query ):',
            [
                'sql' => $this->format($this->sql, $this->params),
                'time'=> number_format(microtime(true) - $this->start, 4),
            ]
        );
        // ($this->queryNumber * -1)  // priority
    }

    /**
     * Return to last sql query string
     *
     * @param string $sql sql
     *
     * @return void
     */
    public function format($sql, $params = array())
    {
        $sql = preg_replace('/\n\r\t/', ' ', trim($sql, "\n"));
        $newValues = array();
        if (! empty($params)) {
            $firstKey = key($params);
            if (is_string($firstKey)) { // named parameters
                foreach ($this->params as $key => $value) {
                    $value = is_string($value) ? "'".addslashes($value)."'" : $value;
                    $sql = str_replace(':'.$key, $value, $sql);
                }
                return $sql;
            }
            if (is_numeric($firstKey)) { // numeric parameters
                foreach ($params as $key => $value) {
                    if (is_string($value)) {
                        $newValues[$key] = "'".addslashes($value)."'";
                    } else {
                        $newValues[$key] = $value;
                    }
                }
                $sql = preg_replace('/(?:[?])/', '%s', $sql);  // question mark binds
                $sql = vsprintf($sql, $newValues);
            }
        }
        return $sql;
    }
}