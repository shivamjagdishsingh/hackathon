<?php
class DB{
    private static $_instance = null;
    private $_pdo,
            $_query,
            $_errors = false,
            $_count = 0,
            $_result;

    private function __construct()
    {
        try{
            $this->_pdo = new PDO('mysql:host=' . config::get('mysql/host') . ';dbname=' . config::get('mysql/database'), config::get('mysql/username') , config::get('mysql/password'));
        }
        catch(PDOException $e)
        {
            die($e->getMessage());
        }
    }

    public static function getInstance()
    {
        if(!isset(self::$_instance))
        {
            self::$_instance = new DB();
        }
        return self::$_instance; 
    }



    // query('insert into users username values ?', arrya('niohar'))

    public function query($sql, $params = array())
    {
        $this->_errors = false;
        if($this->_query = $this->_pdo->prepare($sql))
        {
            $x = 1;
            if(count($params))
            {
                foreach($params as $param)
                {
                    $this->_query->bindValue($x, $param);
                    $x++;
                }
            }

            if($this->_query->execute())
            {
                $this->_result = $this->_query->fetchAll(PDO::FETCH_OBJ);
                $this->_count = $this->_query->rowCount();
            }
            else{
                $this->_errors = true;
            }
        }
        return $this;
    }


    private function action($action, $table, $where= array())
    {
        if(count($where) === 3)
        {
            $operators = array('=', '<', '<=', '>', '>=');

            $field = $where[0];
            $operator = $where[1];
            $value = $where[2];

            if(in_array($operator, $operators))
            {
                $sql = "{$action} FROM {$table} WHERE {$field} {$operator} ?";
                if(!$this->query($sql, array($value))->error())
                {
                    return $this;
                }
            }
        }
        return false;
    }


    public function update($table, $id, $fields = array())
    {
        $x = 1;
        $set = '';

        foreach($fields as $field => $value)
        {
            $set .= "{$field} = ?";

            if($x < count($fields))
            {
                $set .= ', ';
            }
            $x++;
        }

    $sql = "UPDATE {$table} SET {$set} WHERE id = {$id}";

    if(!$this->query($sql, $fields)->error())
    {
        return true;
    }
    return false;
    
    }


    public function insert($table, $fields)
    {
        if(count($fields))
        {
            $keys = array_keys($fields);
            $values = '';
            $x =1;

            foreach($fields as $field)
            {
                $values .= '?';
                if($x < count($fields))
                {
                    $values .= ', ';
                }
                $x++;
            }


            $sql = "INSERT INTO {$table} (`" . implode("`, `", $keys) . "`) VALUES ({$values})";

            // die($sql);

            if(!$this->query($sql, $fields)->error())
            {
                return true;
            }
        }
        return false;
    }

    public function get($table, $where)
    {
       return $this->action("SELECT *", $table, $where);
    }


    public function delete($table, $where)
    {
        return $this->action("DELETE", $table, $where);
    }

    public function first()
    {
        return $this->results()[0];
    }




    public function count()
    {
        return $this->_count;
    }

    public function results()
    {
        return $this->_result;
    }

    public function error(){
        return $this->_errors;
    }
}