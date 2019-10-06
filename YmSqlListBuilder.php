<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2019/7/11
 * Time: 16:59
 */

interface Builder
{
    function build();
}

class YmSqlListBuilder implements Builder
{
    public $out_sql;
    public $list;
    public $list2;
    protected $length;
    protected $quote;
    protected $delimiter;
    protected $left_brace;
    protected $right_brace;

    function __construct($quote, $delimiter, $left_brace, $right_brace)
    {

        $this->out_sql = '';
        $this->list = [];
        $this->list2 = [];
        $this->length = 0;
        $this->quote = $quote;
        $this->delimiter = $delimiter;
        $this->left_brace = $left_brace;
        $this->right_brace = $right_brace;
    }

    function build()
    {
        $this->out_sql = '';
        foreach ($this->list as $index) {
            $this->out_sql =
                $this->out_sql . $this->quote . $index . $this->quote . $this->delimiter;
        }
        $this->out_sql = preg_replace('/\\' . $this->delimiter . "$/", '', $this->out_sql);
        $this->out_sql = $this->left_brace . $this->out_sql . $this->right_brace;
        return 0;
    }

    function clear()
    {
        $this->out_sql = '';
        $this->list = [];
        $this->list2 = [];
        $this->length = 0;
    }

    function __toString()
    {
        $this->build();
        return $this->out_sql;
    }

    /**
     * @return mixed
     */
    public function getLength()
    {
        $this->length = count($this->list);
        return $this->length;
    }
}

class YmSqlColBuilderI extends YmSqlListBuilder
{
    function __construct($quote = '`')
    {
        parent::__construct($quote, ',', '(', ')');
    }
}

class YmSqlColBuilderS extends YmSqlListBuilder
{
    function __construct()
    {
        parent::__construct('`', ',', '', '');
    }
}

class YmSqlValueBuilder extends YmSqlListBuilder
{
    function __construct($quote = '\'')
    {
        parent::__construct($quote, ',', ' VALUE (', ')');
    }
}

class YmSqlWhereBuilder extends YmSqlListBuilder
{
    function __construct($delimiter = ' AND ')
    {
        parent::__construct('', $delimiter, 'WHERE ', '');
    }
}

class YmSqlJoinBuilder extends YmSqlListBuilder
{
    function __construct()
    {
        parent::__construct('`', ' LEFT JOIN ', '', '');
    }

    function build()
    {
        $this->out_sql = '';
        $length = $this->getLength();
        for ($index = 0; $index < $length; $index++) {
            $this->out_sql =
                $this->out_sql . $this->delimiter . $this->quote . $this->list[$index] . $this->quote . ' ON ' . $this->list2[$index];
        }
        return 0;
    }
}

abstract class YmSubSqlBuilder
{
    public $value;
    public $quote_left;
    public $quote_right;
    protected $out_sql;
    protected $extra;

    function __construct($value = '', $quote = '', ...$extra)
    {
        $this->extra = $extra;
        $this->value = $value;
        if ($quote == '(') {
            $this->quote_left = '(';
            $this->quote_right = ')';
        } else {
            $this->quote_left = $quote;
            $this->quote_right = $quote;
        }
        $this->subConstruct();
    }

    function __toString()
    {
        $this->out_sql = '';
        $this->out_sql .= $this->quote_left . $this->value . $this->quote_right;
        $this->subBuild();
        return $this->out_sql;
    }

    abstract function subConstruct();

    abstract function subBuild();
}

class YmSubColBuilder extends YmSubSqlBuilder
{
    public $as;

    function subConstruct()
    {
        $this->as = $this->extra[0];
    }

    function subBuild()
    {
        if ($this->as) {
            $this->out_sql .= ' as `' . $this->as . '`';
        }
    }
}

class YmSubColBuilder2
{
    public $as;
    public $value;
    public $quote_left;
    public $quote_right;

    function __construct($value = '', $quote = '', $as = '')
    {
        $this->as = $as;
        $this->value = $value;
        if ($quote == '(') {
            $this->quote_left = '(';
            $this->quote_right = ')';
        } else {
            $this->quote_left = $quote;
            $this->quote_right = $quote;
        }
    }

    function __toString()
    {
        $out_sql = '';
        $out_sql .= $this->quote_left . $this->value . $this->quote_right;
        if ($this->as) {
            $out_sql .= ' as `' . $this->as . '`';
        }
        return $out_sql;
    }
}


class YmAdvSqlBuilder
{
    private $sub_builder_list;
    private $builder_class_name;
    private $delimiter;

    function __construct($builder_class_name, $delimiter = ',')
    {
        $this->delimiter = $delimiter;
        $this->sub_builder_list = [];
        $this->builder_class_name = $builder_class_name;
    }

    function add($value = '', $quote = '', ...$extra)
    {
        array_push($this->sub_builder_list, new $this->builder_class_name($value, $quote, ...$extra));
    }

    function __toString()
    {
        $return_str = '';
        foreach ($this->sub_builder_list as $index) {
            $return_str .= $index . $this->delimiter;
        }
        $return_str = preg_replace('/\\'.$this->delimiter.'$/', '', $return_str);
        return $return_str;
    }
}

class YmAdvColBuilder
{
    public $sub_builder_list;

    function __construct()
    {
        $this->sub_builder_list = [];
    }

    function add($value = '', $quote = '', $as = '')
    {
        array_push($this->sub_builder_list, new YmSubColBuilder($value, $quote, $as));
    }

    function __toString()
    {
        $return_str = '';
        foreach ($this->sub_builder_list as $index) {
            $return_str .= $index . ',';
        }
        $return_str = preg_replace('/,$/', '', $return_str);
        return $return_str;
    }


}
