<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2019/6/2
 * Time: 0:05
 */

class YmBaseMysql
{
    var $mysqli;
    var $ym_result;
    var $raw_result;//原始返回result
    var $status;
    var $value;

    function __construct($host, $username, $pwd, $dbname)
    {
        $this->mysqli = new mysqli($host, $username, $pwd, $dbname);
        if ($this->mysqli->connect_error) {
            die("连接失败: " . $this->mysqli->connect_error);
        }
        $this->mysqli->set_charset("utf8");
    }


    function ym_query($sql)
    {
        $result = $this->mysqli->query($sql, MYSQLI_STORE_RESULT);
        //没select时result为布尔值，有select则为object,有select无结果也为object,call的结果可为bool
        if (!$result) {
            //原始返回result为空
            $this->raw_result = null;
            //错误返回1
            $this->status = 1;
            $this->value = $this->mysqli->errno . ': ' . $this->mysqli->error;
            return 1;
        }

        if (is_object($result)) {
            //有结果返回0
            //原始返回result为$result
            $this->raw_result = $result;
            if ($result->num_rows != 0) {
                $output_arr = array();
                while ($row = $result->fetch_assoc()) {
                    array_push($output_arr, $row);
                }

                //外国大佬的解决方案
                do {
                    if ($res = $this->mysqli->store_result()) {
                        $res->free();
                    }
                } while ($this->mysqli->more_results() && $this->mysqli->next_result());
                //外国大佬的解决方案
                $this->status = 0;
                $this->value = $output_arr;

                return 0;
            } else {
                //返回结果为空返回2，不带值
                $this->status = 2;
                $this->value = null;
                return 2;
            }
        } else {
            //空结果返回2，不带值
            $this->status = 2;
            $this->value = null;
            return 2;
        }
    }

    /**
     * 运行队列，注意只能运行select
     * @param $sql
     * @return array contains object
     */
    function ym_multi_query($sql)
    {
        $out_array = [];
        if ($this->mysqli->multi_query($sql)) {
            do {
                $out_array_sub = [];
                //store_result成功则返回一个缓冲的结果集对象，失败则返回 FALSE。
                $result = $this->mysqli->store_result();
//                var_dump($result);
                if ($result) {
                    //复制结果
                    while ($row = $result->fetch_assoc()) {
                        array_push($out_array_sub, $row);
                    }

                    array_push($out_array, $out_array_sub);
                    $result->free();
                }
            } while ($this->mysqli->next_result());//如果错误直接返回false
        }
        return $out_array;
    }


    function close()
    {
        $this->mysqli->close();
    }



}

class YmOhincMysql extends YmBaseMysql
{
    protected function ym_return_list($error, $value)
        /**
         * 组装错误编号和返回值成字典
         */
    {
        return ['error' => $error, 'value' => $value];
    }

    function select_value($name)
    {
        $sql = <<<SQL
SELECT `{$name}_value` 
FROM `{$name}_dict_t`
order by `{$name}_value` asc 
SQL;
        $this->ym_query($sql);
        if ($this->ym_result['error'] == 0) {
            $num = count($this->ym_result['value']);
            $output_arr = array();
            for ($i = 0; $i < $num; $i++) {
                array_push($output_arr, $this->ym_result['value'][$i][$name . '_value']);
            }
            return $this->ym_return_list(0, $output_arr);
        }
        return $this->ym_result;
    }
}