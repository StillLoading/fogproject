<?php
class MySQL extends DatabaseManager {
    /** @var $link the link after connected */
    private $link;
    /** @var $query the query to call */
    private $query;
    /** @var $queryResult the result of the query */
    private $queryResult;
    /** @var $result the result set */
    private $result;
    /** __construct initializes the class
     * @return void
     */
    public function __construct() {
        parent::__construct();
        try {
            if (!class_exists('mysqli')) throw new Exception(sprintf('%s PHP extension not loaded', __CLASS__));
            if (!$this->connect()) throw new Exception('Failed to connect');
        } catch (Exception $e) {
            $this->error(sprintf('Failed to %s: %s', __FUNCTION__, $e->getMessage()));
        }
    }
    /** connect establishes the link
     * @return the class
     */
    public function connect() {
        try {
            if (!$this->link = mysqli_init()) die(_('Could not initialize mysqli'));
            if (!$this->link->real_connect(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD)) die(_('MySQL does not appear to be running'));
            if ($this->link->connect_error) throw new Exception(sprintf('Host: %s, Username: %s, Database: %s, Error: %s', DATABASE_HOST, DATABASE_USERNAME, DATABASE_NAME, $this->sqlerror));
            if (!$this->link->select_db(DATABASE_NAME)) throw new Exception(_('Issue working with the current DB, maybe it has not been created yet'));
            $this->link->set_charset('utf8');
        } catch (Exception $e) {
            if (strstr($e->getMessage(),'MySQL server has gone away')) $this->connect();
            else $this->debug(sprintf('Failed to %s: %s', __FUNCTION__, $e->getMessage()));
        }
        return $this;
    }
    /** query performs the db query
     * @param string sql
     * @param array data
     * @return this class
     */
    public function query($sql, $data = array()) {
        try {
            $this->queryResult = null;
            if (!is_array($data)) $data = array($data);
            if (count($data)) $sql = vsprintf($sql,$data);
            $this->info($sql);
            $this->query = $sql;
            if (!$this->query) throw new Exception(_('No query sent'));
            if (!$this->link) $this->connect();
            if ($_REQUEST['node'] == 'schemaupdater') $sqlMethod = 'query';
            else $sqlMethod = 'prepare';
            if (!$this->queryResult = $this->link->$sqlMethod($this->query)) throw new Exception(_('Error: ').$this->sqlerror());
            if ($sqlMethod == 'prepare') {
                $this->queryResult->execute();
                $this->queryResult = $this->queryResult->get_result();
            }
        } catch (Exception $e) {
            $this->debug(sprintf('Failed to %s: %s', __FUNCTION__, $e->getMessage()));
        }
        return $this;
    }
    /** fetch() fetches the data
     * @param $type what type of data to fetch in
     * @return the class as is
     */
    public function fetch($type = MYSQLI_ASSOC,$fetchType = 'fetch_assoc') {
        try {
            if (empty($this->queryResult)) throw new Exception(_('No query result, use query() first'));
            $this->result = array();
            if (empty($type)) $type = MYSQLI_ASSOC;
            if (empty($fetchType)) $fetchType = 'fetch_assoc';
            if (in_array($this->queryResult,array(true,false),true)) $this->result = $this->queryResult;
            else if (!is_object($this->queryResult)) $this->result = $this->link;
            else if ($fetchType == 'fetch_assoc') $this->result = $this->queryResult->fetch_assoc();
            else if ($fetchType == 'fetch_array') $this->result = $this->queryResult->fetch_array();
            else if ($fetchType == 'fetch_all') {
                if (method_exists('mysqli_result','fetch_all')) $this->result = $this->queryResult->fetch_all($type);
                else for ($this->result = array();$tmp = $this->queryResult->fetch_array($type);) $this->result[] = $tmp;
            }
        } catch (Exception $e) {
            $this->debug(sprintf('Failed to %s: %s', __FUNCTION__, $e->getMessage()));
        }
        return $this;
    }
    /** get() get the information as called
     * @param $field the field to get or all
     * @return the requested data or all
     */
    public function get($field = '') {
        if ($this->result === true) return $this->result;
        try {
            if ($this->result === false) throw new Exception(_('No data returned'));
        } catch (Exception $e) {
            $this->debug(sprintf('Failed to %s: %s', __FUNCTION__, $e->getMessage()));
            return false;
        }
        $result = array();
        foreach ((array)$field AS $i => &$key) {
            $key = trim($key);
            if (array_key_exists($key, (array)$this->result)) return $this->result[$key];
            foreach ((array)$this->result AS $i => &$value) {
                if (array_key_exists($key, (array)$value)) $result[] = $value[$key];
            }
        }
        if (count($result)) return $result;
        return $this->result;
    }
    /** result() result of the query
     * @return the result
     */
    public function result() {
        return $this->result;
    }
    /** queryResult() queryResult of the sql query
     * @return the queryResult
     */
    public function queryResult() {
        return $this->queryResult;
    }
    /** sqlerror() the error if there is one
     * @return the connection or sql error
     */
    public function sqlerror() {
        return $this->link->connect_error ? $this->link->connect_error.', Message: '.'Check that database is running' : $this->link->error;
    }
    /** insert_id() the last insert id
     * @return the value of the id
     */
    public function insert_id() {
		$insert_id = $this->queryResult()->insert_id;
		if (intval($insert_id) <= 0) $insert_id = $this->link->insert_id;
		if (intval($insert_id) <= 0) throw new Exception(_('No insert id found'));
		return (int)$insert_id;
    }
    /** affected_rows() the number of affected rows
     * @return the number
     */
    public function affected_rows() {
		$affected_rows = $this->queryResult()->affected_rows;
		if (intval($affected_rows) <= 0) $affected_rows = $this->link->affected_rows;
		if (intval($affected_rows) <= 0) throw new Exception(_('No affected rows found'));
		return (int)$affected_rows;
    }
    /** num_rows() the number of rows.
     * @return the number
     */
    public function num_rows() {
		$num_rows = $this->queryResult()->num_rows;
		if (intval($num_rows) <= 0) $num_rows = $this->link->num_rows;
		if (intval($num_rows) <= 0) throw new Exception(_('No num rows found'));
		return (int)$num_rows;
    }
    /** escape() escape/clean the data
     * @param $data the data to be cleaned
     * @return the sanitized data
     */
    public function escape($data) {
        return $this->sanitize($data);
    }
    /** clean() escape/clean the data
     * @param $data the data to be cleaned
     * @return the sanitized data
     */
    private function clean($data) {
        return $this->link->real_escape_string(stripslashes(addslashes(strip_tags($data))));
    }
    /** sanitize() escape/clean the data
     * @param $data the data to be cleaned
     * @return the sanitized data
     */
    public function sanitize($data) {
        if (!is_array($data)) return $this->clean($data);
        foreach ($data AS $key => &$val) {
            if (is_array($val)) $data[$this->clean($key)] = $this->escape($val);
            else $data[$this->clean($key)] = $this->clean($val);
        }
        return $data;
    }
    /** link() returns the link as is
     * @return the link as connected
     */
    public function link() {
        return $this->link;
    }
}
