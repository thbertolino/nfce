<?php
/**
 * Classe DB
 *
 * @autor Rafael Clares  <rafael@clares.com.br>  2017
 **/

Class DB
{

    public $host;
    public $base;
    public $user;
    public $pass;
    public $con;
    public $sql;
    public $query;
    public $res;
    public $data;
    public $lastId;
    public $prefix = false;
    public $count = 0;
    public $rows;
    public $page = 0;
    public $perpage = 10;
    public $current = 1;
    public $test = 0;
    public $url = null;
    public $baseurl = null;
    public $link = '';
    public $total = '';
    public $pagination = false;
    public $connectionName;


    public function __construct($connectionName = 'database')
    {
        //global $database;
        //require_once Path::base() . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "database.php";
        $config = parse_ini_file('config/database.conf', 1);
        if (!isset($config[$connectionName])) {
            throw new \Exception("Erro de configuração, {$connectionName} não encontrado");
        }
        $database = $config[$connectionName];
        $this->host = $database['host'];
        $this->connectionName = $connectionName;
        $this->base = $database['base'];
        $this->user = $database['user'];
        $this->pass = $database['pass'];
        $this->port = $database['port'];
        $this->prefix = $database['prefix'];
        $this->con = $this->open();
    }

    public function open()
    {
        $registry = Registry::getInstance($this->connectionName);
        try {
            if ($registry->get('pdo') == false) {
                $this->con = new PDO("mysql:host=$this->host;port=$this->port;dbname=$this->base", $this->user, $this->pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                $this->con->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
                $this->con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $this->con->exec("SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));");
                //$this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //CAUSE ERROR 500
                $registry->set('pdo', $this->con);
            }
            $this->con = $registry->get('pdo');
        } catch (PDOException $e) {
            if ($e->getCode() === 1049) {
                //die(('Banco de dados ['.$this->base.'] não encontrado! <br> Verifique o arquivo config/database.php'));
                (new Page)->_erro(('Banco de dados [' . $this->base . '] não encontrado! <br> Verifique o arquivo config/database.conf'))->_and_stop();
            }
            if ($e->getCode() === 1045) {
                //die(utf8_decode('Dados de acesso (usuário/senha) incorretos para base ['.$this->base.']. <br> Verifique o arquivo config/database.php'));
                (new Page)->_erro(('Dados de acesso (usuário/senha) ao banco de dados, incorretos!. <br> Verifique o arquivo config/database.conf'))->_and_stop();
            }
            if ($e->getCode() === 2005) {
                //die(utf8_decode('Endereço do servidor SQL ['.$this->host.'] incorreto. <br> Verifique o parâmetro "host" no arquivo config/database.php'));
                (new Page)->_erro(('Endereço do servidor SQL [' . $this->host . '] incorreto. <br> Verifique o parâmetro "host" no arquivo config/database.conf'))->_and_stop();
            }
            //die($e->getMessage());
            (new Page)->_erro($e->getMessage())->_and_stop();
        }
        return $this->con;
    }

    public function getCon()
    {
        return $this->con;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close()
    {
        if (isset($this->res) && $this->res != null) {
            $this->res->closeCursor();
        }
    }

    public function query($sql)
    {
        $this->res = $this->con->prepare($sql) or die(print_r($this->con->errorInfo()[2], true));
        return $this->res->execute();
    }

    public function lastId()
    {
        $this->lastId = $this->con->lastInsertId();
        return $this->lastId;
    }

    public function fetch($sql = null, $typeFetch = null)
    {
        if ($sql != null) {
            $this->query = $sql;
        }
        if ($this->pagination == true) {
            $this->res = $this->con->preapare($this->query) or die($this->con->errorInfo()[2]);
            $this->res->execute();
            $this->rows = $this->res->rowCount();
            $this->query .= " LIMIT $this->page, $this->perpage";
            $this->pagination = false;
        }
        $this->res = $this->con->prepare($this->query) or die(print_r($this->con->errorInfo()[2], true));
        $this->res->execute();

        if (is_null($typeFetch)) {
            $typeFetch = PDO::FETCH_OBJ;
        }
        $this->data = $this->res->fetchAll($typeFetch);
        return $this->data;
    }

    public function add_columns($table, $columns)
    {
        if ($this->prefix) {
            $this->prefix = $table . "_";
        }
        $db_name = $this->base;
        foreach ($columns as $f) {
            $f = (object)$f;
            $exist = self::fetch("SELECT NULL FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$table' AND table_schema = '$db_name' AND column_name = '{$this->prefix}{$f->name}'");
            if (!isset($exist[0])) {
                $sql = "ALTER TABLE $table ADD COLUMN ";
                if (isset($f->null)) {
                    $f->null = '$f->null';
                } else {
                    $f->null = '';
                }
                if (isset($f->default)) {
                    $f->default = "DEFAULT $f->default";
                } else {
                    $f->default = '';
                    if ($f->null == '') {
                        $f->default = 'DEFAULT NULL';
                    }
                }
                if (isset($f->key)) {
                    $sql .= trim(rtrim($this->prefix . "$f->name $f->type NOT NULL PRIMARY KEY AUTO_INCREMENT")) . ";\n";
                } else {
                    $sql .= trim(rtrim($this->prefix . "$f->name $f->type $f->default")) . ";\n";
                }
                if (isset($f->reference)) {
                    $sql .= trim(rtrim("FOREIGN KEY($f->name) REFERENCES $f->reference")) . ";\n";
                }
                if ($this->test) {
                    echo "TESTE: ", $sql . "\n\n\n\n\n";
                } else {
                    echo $sql . "\n\n";
                    $this->query($sql);
                }
            } else {
                //echo "<br>[<b>$table</b>] Migração já executada!\n";
            }
        }

    }

    public function drop_column($table, $column)
    {
        $column = (object)$column;
        $db_name = $this->base;
        $this->query = "SELECT COLUMN_NAME, COLUMN_DEFAULT, COLUMN_TYPE FROM 
                            INFORMATION_SCHEMA.COLUMNS WHERE 
                            TABLE_SCHEMA = '$db_name' AND 
                            TABLE_NAME = '$table' AND 
                            COLUMN_NAME  = '$column->name' ";
        $orm = $this->fetch();
        if (!empty($orm)) {
            $sql = "ALTER TABLE DROP COLUMN $column->name;";
            $this->query($sql);
        } else {
            echo "<br> Coluna <strong>$column->name</strong> inexistente <br>";
        }
    }

    public function alter_column($table, $column)
    {
        //ESCOPO
        //$data = ['name' => 'campo', 'new' => 'novo_campo', 'type' => 'int(11)', 'default' => 'foo'];
        $column = (object)$column;
        $db_name = $this->base;
        $this->query = "SELECT COLUMN_NAME, COLUMN_DEFAULT, COLUMN_TYPE FROM 
                            INFORMATION_SCHEMA.COLUMNS WHERE 
                            TABLE_SCHEMA = '$db_name' AND 
                            TABLE_NAME = '$table' AND 
                            COLUMN_NAME  = '$column->name' ";
        $orm = $this->fetch();
        if (!empty($orm)) {
            $orm = $orm[0];
            $sql = "ALTER TABLE $table CHANGE $column->name";
            !empty($column->new) ? $sql .= " $column->new" : $sql .= " $orm->COLUMN_NAME";
            !empty($column->type) ? $sql .= " $column->type" : $sql .= " $orm->COLUMN_TYPE";
            !empty($column->default) ? $sql .= " $column->default" : $sql .= " $orm->COLUMN_DEFAULT";
            $this->query($sql);
            echo "<br> Coluna <strong>$column->name</strong> foi alterada para <strong>$column->new</strong> <br>";
            return true;
        }
    }

    public function show_columns($table)
    {
        $defaults = ['CURRENT_TIMESTAMP'];
        $db_name = $this->base;
        $this->query = "SELECT COLUMN_NAME, COLUMN_DEFAULT FROM 
                            INFORMATION_SCHEMA.COLUMNS WHERE 
                            TABLE_SCHEMA = '$db_name' AND 
                            TABLE_NAME = '$table'";
        $orm = $this->fetch();
        $obj = new stdClass;
        foreach ($orm as $o) {
            if ($o->COLUMN_DEFAULT != "") {
                $obj->{$o->COLUMN_NAME} = (!in_array($o->COLUMN_DEFAULT, $defaults)) ? $o->COLUMN_DEFAULT : '';
            } else {
                $obj->{$o->COLUMN_NAME} = '';
            }
        }
        return $obj;
    }

    public function drop_table($table)
    {
        $sql = "DROP TABLE IF EXISTS $table;";
        $this->query($sql);
    }

    public function check($sql)
    {
        return $this->con->prepare($sql);
    }

    public function show_table($table, $txt = false)
    {
        $db_name = $this->base;
        $this->query = "SELECT * FROM 
                INFORMATION_SCHEMA.COLUMNS WHERE 
                TABLE_SCHEMA = '$db_name' AND 
                TABLE_NAME = '$table'";
        $orm = $this->fetch();
        $obj = new stdClass;
        $text = "";
        foreach ($orm as $o) {
            $obj->{$o->COLUMN_NAME} = $o->COLUMN_TYPE;
            $text .= "$o->COLUMN_NAME = $o->COLUMN_TYPE";
            if ($o->COLUMN_DEFAULT != "") {
                $obj->{$o->COLUMN_NAME} .= ' DEFAULT ' . "'" . $o->COLUMN_DEFAULT . "'";
                $text .= ' DEFAULT ' . "'" . $o->COLUMN_DEFAULT . "'";
            }
            $text .= "\n";
        }
        if ($txt) {
            return $text;
        } else {
            return $obj;
        }
    }

    public function create_table($table, $columns, $add = false)
    {
        if ($this->prefix) {
            $this->prefix = $table . "_";
        }
        $sql = "CREATE TABLE IF NOT EXISTS $table(\n";
        foreach ($columns as $f) {
            $f = (object)$f;
            if (!isset($f->null)) {
                $f->null = 'NULL';
            }
            if (isset($f->default)) {
                $f->default = "DEFAULT $f->default";
            } else {
                $f->default = '';
            }
            if (isset($f->key)) {
                $sql .= trim(rtrim($this->prefix . "$f->name $f->type NOT NULL PRIMARY KEY AUTO_INCREMENT")) . ",\n";
            } else {
                $sql .= trim(rtrim($this->prefix . "$f->name $f->type $f->default")) . ",\n";
            }
            if (isset($f->reference)) {
                $sql .= trim(rtrim("FOREIGN KEY($f->name) REFERENCES $f->reference")) . ",\n";
            }
        }
        $sql .= $this->prefix . "created timestamp DEFAULT '0000-00-00 00:00:00',\n";
        $sql .= $this->prefix . "updated timestamp DEFAULT NOW() ON UPDATE NOW()\n";
        $sql .= ")ENGINE = InnoDB DEFAULT CHARSET=utf8;";
        if ($this->test) {
            echo $sql . "\n\n\n\n\n";
        } else {
            $this->query($sql) or die("Erro ao tentar criar $table\n $sql");
        }
        if ($add) {
            if (!(new Factory($table))->limit(1)->get()) {
                (new Factory($table))->with($add)->save();
            }
        }

    }

}
