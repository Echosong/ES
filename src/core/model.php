<?php


class Model
{
    public $page;
    public $table_name;

    private $_master_db;
    private $_slave_db;
    private $sql = array();

    public function __construct($table_name = null)
    {
        global $GLOBALS;
        if ($table_name) {
            $this->table_name = $GLOBALS['prefix'] . $table_name;
        }
    }

    public function findAll($conditions = array(), $sort = null, $fields = '*', $limit = null)
    {
        $sort = !empty($sort) ? ' ORDER BY ' . $sort : '';
        $conditions = $this->_where($conditions);
        $sql = ' FROM ' . $this->table_name . $conditions["_where"];
        if (is_array($limit)) {
            $total = $this->query('SELECT COUNT(*) as M_COUNTER ' . $sql, $conditions["_bindParams"]);
            $limit = $limit + array(1, 20, 10);
            $limit = $this->pager($limit[0], $limit[1], $limit[2], $total[0]['M_COUNTER']);
            $limit = empty($limit) ? '' : ' LIMIT ' . $limit['offset'] . ',' . $limit['limit'];
        } else {
            $limit = !empty($limit) ? ' LIMIT ' . $limit : '';
        }
        return $this->query('SELECT ' . $fields . $sql . $sort . $limit, $conditions["_bindParams"]);
    }

    public function find($conditions = array(), $sort = null, $fields = '*')
    {
        $res = $this->findAll($conditions, $sort, $fields, 1);
        return !empty($res) ? array_pop($res) : false;
    }

    public function update($conditions, $row)
    {
        $values = array();
        foreach ($row as $k => $v) {
            $values[":M_UPDATE_" . $k] = $v;
            $setstr[] = '`' . $k . "`=" . ":M_UPDATE_" . $k;
        }
        $conditions = $this->_where($conditions);
        return $this->execute("UPDATE " . $this->table_name . " SET " . implode(', ', $setstr) . $conditions["_where"],
            $conditions["_bindParams"] + $values);
    }

    public function delete($conditions)
    {
        $conditions = $this->_where($conditions);
        return $this->execute("DELETE FROM " . $this->table_name . $conditions["_where"], $conditions["_bindParams"]);
    }

    public function create($row)
    {
        $values = array();
        foreach ($row as $k => $v) {
            $keys[] = "`{$k}`";
            $values[":" . $k] = $v;
            $marks[] = ":" . $k;
        }
        $this->execute("INSERT INTO " . $this->table_name . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ',
                $marks) . ")", $values);
        return $this->_master_db->lastInsertId();
    }

    public function creates($rows)
    {
        if (!is_array($rows[0]) || empty($rows[0])) {
            $this->create($rows);
        }
        if (!($this->_master_db)) {
            $this->setDB('default');
        }
        foreach ($rows[0] as $k => $v) {
            $keys[] = "`{$k}`";
            $marks[] = ":" . $k;
        }
        $sql = "INSERT INTO " . $this->table_name . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ',
                $marks) . ")";
        $this->_master_db->beginTransaction();

        $sth = $this->_master_db->prepare($sql);
        foreach ($rows as $row) {
            $values = [];
            foreach ($row as $k => $v) {
                $values[":" . $k] = $v;
            }
            $sth->execute($values);
        }
        $this->_master_db->commit();
    }

    public function findCount($conditions)
    {
        $conditions = $this->_where($conditions);
        $count = $this->query("SELECT COUNT(*) AS M_COUNTER FROM " . $this->table_name . $conditions["_where"],
            $conditions["_bindParams"]);
        return isset($count[0]['M_COUNTER']) && $count[0]['M_COUNTER'] ? $count[0]['M_COUNTER'] : 0;
    }

    public function findSum($conditions, $field)
    {
        $conditions = $this->_where($conditions);
        $sum = $this->query("SELECT sum({$field}) AS M_COUNTER FROM " . $this->table_name . $conditions["_where"],
            $conditions["_bindParams"]);
        return isset($sum[0]['M_COUNTER']) && $sum[0]['M_COUNTER'] ? $sum[0]['M_COUNTER'] : 0;
    }

    public function dumpSql()
    {
        return $this->sql;
    }

    public function pager($page, $pageSize = 10, $scope = 10, $total)
    {
        $this->page = null;
        if ($total > $pageSize) {
            $total_page = ceil($total / $pageSize);
            $page = min(intval(max($page, 1)), $total);
            $this->page = array(
                'total_count' => $total,
                'page_size' => $pageSize,
                'total_page' => $total_page,
                'first_page' => 1,
                'prev_page' => ((1 == $page) ? 1 : ($page - 1)),
                'next_page' => (($page == $total_page) ? $total_page : ($page + 1)),
                'last_page' => $total_page,
                'current_page' => $page,
                'all_pages' => array(),
                'offset' => ($page - 1) * $pageSize,
                'limit' => $pageSize,
            );
            $scope = (int)$scope;
            if ($total_page <= $scope) {
                $this->page['all_pages'] = range(1, $total_page);
            } elseif ($page <= $scope / 2) {
                $this->page['all_pages'] = range(1, $scope);
            } else {
                $this->page['all_pages'] = range($page - $scope / 2, min($page + $scope / 2 - 1, $total_page));
            }
        }
        return $this->page;
    }

    public function query($sql, $params = array())
    {
        return $this->execute($sql, $params, true);
    }

    public function execute($sql, $params = array(), $is_query = false)
    {
        $this->sql[] = $sql;
        if ($is_query && is_object($this->_slave_db)) {
            $sth = $this->_slave_db->prepare($sql);
        } else {
            if (!($this->_master_db)) {
                $this->setDB('default');
            }
            $sth = $this->_master_db->prepare($sql);
        }

        if (is_array($params) && !empty($params)) {
            foreach ($params as $k => &$v) {
                $sth->bindParam($k, $v);
            }
        }
        if ($sth->execute()) {
            return $is_query ? $sth->fetchAll(PDO::FETCH_ASSOC) : $sth->rowCount();
        }
        $err = $sth->errorInfo();
        Helper::log('Database SQL: "' . $sql . '", ErrorInfo: ' . $err[2], "error");
    }

    public function setDB($db_config_key = 'default', $is_readonly = false)
    {
        if ('default' == $db_config_key) {
            $db_config = $GLOBALS['mysql']['master'];
        } else {
            if (!empty($GLOBALS['mysql'][$db_config_key])) {
                $db_config = $GLOBALS['mysql'][$db_config_key];
            } else {
                Helper::log("Database Err: Db config '$db_config_key' is not exists!", "error");
            }
        }
        if ($is_readonly) {
            $this->_slave_db = $this->_db_instance($db_config, $db_config_key);
        } else {
            $this->_master_db = $this->_db_instance($db_config, $db_config_key);
        }
    }

    private function _db_instance($db_config, $db_config_key)
    {
        if (empty($GLOBALS['mysql_instances'][$db_config_key])) {
            try {
                $GLOBALS['mysql_instances'][$db_config_key] = new PDO('mysql:dbname=' . $db_config['MYSQL_DB'] . ';host=' . $db_config['MYSQL_HOST'] . ';port=' . $db_config['MYSQL_PORT'],
                    $db_config['MYSQL_USER'], $db_config['MYSQL_PASS'],
                    array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'' . $db_config['MYSQL_CHARSET'] . '\''));
            } catch (PDOException $e) {
                Helper::log('Database Err: ' . $e->getMessage(), "error");
            }
        }
        return $GLOBALS['mysql_instances'][$db_config_key];
    }

    private function _where($conditions)
    {
        $result = array("_where" => " ", "_bindParams" => array());
        if (empty($conditions)) {
            return $result;
        }
        if (is_array($conditions) && !empty($conditions)) {
            $sql = null;
            $join = array();
            if (array_values($conditions) === $conditions){
                $sql = $conditions[0][0];
                $conditions = $conditions[1];
            }else{
                foreach ($conditions as $key => $condition) {
                    $optStr = substr($key, strlen($key) - 1, 1);
                    if ($optStr == '>' || $optStr == '<') {
                        unset($conditions[$key]);
                        $key = str_replace($optStr, '', $key);
                    } else {
                        $optStr = '=';
                    }
                    if (substr($key, 0, 1) != ":") {
                        unset($conditions[$key]);
                        $conditions[":" . $key] = $condition;
                    }
                    $join[] = "`{$key}`{$optStr} :{$key}";
                }if (!$sql) {
                    $sql = join(" AND ", $join);
                }
            }
            $result["_where"] = " WHERE " . $sql;
            $result["_bindParams"] = $conditions;
        } else {
            $result["_where"] = " WHERE " . $conditions;
            $result["_bindParams"] = array();
        }
        return $result;
    }
}