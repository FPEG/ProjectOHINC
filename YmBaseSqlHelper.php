<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2019/6/2
 * Time: 0:02
 */

include 'YmMysql.php';

$test = [
    [
        'source_table' => 'asd',
        'target_table' => 'asd',
        'source_col' => 'asd',
        'target_col' => 'asd'

    ], ['asd' => 'asd']
];

class YmBaseSqlHelper
{
    private $yk_mysql;//ykmysql对象
    private $sql;
    private $table_name;


    function __construct($yk_mysql)
    {
        $this->yk_mysql = $yk_mysql;
//        new YmMysql("localhost", "root", "AAdmin5!>", "ProjectOHINC_db");
        $this->sql = '';
    }

    function add_table_name($table_name)
    {
        $this->table_name = $table_name;
    }

    function get_result()
    {

    }
}

class YmSelectSqlHelper extends YmBaseSqlHelper
{
    private $column_name;

//    private $

    function __construct($yk_mysql)
    {
        parent::__construct($yk_mysql);
        $this->column_name = [];
    }

    function add_column_name($column_name_list)
    {
        foreach ($column_name_list as $column_name) {
            array_push($this->column_name, $column_name);
        }
    }


}

class YmBaseSqlBuilder
{
    protected $list;
    protected $value;
    protected $sql;

    /**
     * YmBaseSqlBuilder constructor.
     *
     */
    function __construct()
    {
        $this->value = '';
        $this->list = [];
        $this->sql = '';
    }

    function __toString()
    {
        return $this->sql;
    }

    function clear()
    {
        $this->value = '';
        $this->list = [];
        $this->sql = '';
    }

    function build()
    {

    }

    function base_set_data($data)
    {
        if (is_string($data)) {
            $this->value = $data;
        }
        if (is_array($data)) {
            foreach ($data as $value) {
                array_push($this->list, $value);
            }
        }
    }

    function set_data($data)
    {
        $this->base_set_data($data);
    }

}

class YmTablenameSqlBuilder extends YmBaseSqlBuilder
{
    function build()
    {
        $sql_part = <<<SQL
`{$this->value}`
SQL;
        $this->sql = $this->sql . $sql_part;
    }
}

class YmColnameSqlBuilder extends YmBaseSqlBuilder
{
    function build()
    {
        $sql_rest = '';
        foreach ($this->list as $value) {
            $sql_part = <<<SQL
`{$value}`,
SQL;
            $sql_rest .= $sql_part;
        }
        $sql_rest = substr($sql_rest, 0, -1);
        $this->sql = $this->sql . $sql_rest;

    }
}

class YmJoinSqlBuilder extends YmBaseSqlBuilder
{
    function build()
    {
        $sql_rest = '';
        foreach ($this->list as $value) {
            $sql_part = <<<SQL
LEFT JOIN `{$value['target_table']}`ON
`{$value['source_table']}`.`{$value['source_col']}`
=
`{$value['target_table']}`.`{$value['target_col']}`
SQL;
            $sql_rest .= $sql_part;
        }
        $this->sql = $this->sql . $sql_rest;
    }
}


