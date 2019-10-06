<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2019/7/16
 * Time: 18:45
 */
include_once 'YmMysql.php';
include_once 'YmSqlListBuilder.php';

class YmWordProperty
{
    public $word;
    public $ymsql;
    public $sql;
    public $result_list;

    public $word_id;
    public $user_id;

    function pushResult($field, $item_type, $field_name, $null_value = '', $debug_value = '', string ...$extra)
    {
        $item = [
            'field' => $field,
            'item_type' => $item_type,
            'field_name' => $field_name,
            'field_value' => [],
        ];
        if ($debug_value) {
            $item['debug'] = $debug_value;
        }
        if (count($this->ymsql->value) == 0) {
            if ($null_value) {
                $push_array = [];
                foreach ($extra as $ext) {
                    $push_array[$ext] = 0;
                }
                $push_array['value'] = $null_value;
                array_push($item['field_value'], $push_array);
            }

        } else {
            foreach ($this->ymsql->value as $row) {
                $push_array['value'] = $row['value'];
                foreach ($extra as $ext) {
                    $push_array[$ext] = $row[$ext];
                }
                array_push($item['field_value'], $push_array);
            }
        }
        array_push($this->result_list, $item);
    }

    function __construct($word)
    {
        $this->user_id = 1;
        $this->result_list = [];
        $this->word = $word;
        $this->ymsql = new YmOhincMysql("localhost", "root", "AAdmin5!>", "ProjectOHINC_db");
        $this->getWord();
//        $this->getWordId();

    }

    function getWordId()
    {

    }

    function getWord()
    {
        $this->sql = <<<SQL
SELECT `id` FROM user_word_t WHERE `value` = '{$this->word}'
AND user_id = {$this->user_id}
SQL;
        $this->ymsql->ym_query($this->sql);
        $this->word_id = $this->ymsql->value[0]['id'];

        $item = [
            'item_type' => 'single',
            'field' => 'word',
            'field_name' => '单词',
            'field_value' => [['value' => $this->word, 'id' => $this->word_id]]
        ];

        array_push($this->result_list, $item);
    }

    function getIpa()
    {

        $this->pushResult('global_ipa','multi','单词音标','','');
    }

    function conUpDown() {
        $item = [
            'field' => 'controller_updown',
            'item_type' => 'multi',
            'field_name' => '上下',
            'field_value' => [],
        ];
        array_push($this->result_list, $item);
    }

    function getUserSource()
    {
        $join = new YmSqlJoinBuilder();
        $join->list = [
            'user_source_t',
        ];
        $join->list2 = [
            'user_word_t.id = user_source_t.user_word_id',
        ];
        $join->build();

        $where = new YmSqlWhereBuilder();
        $where->list = [
            '`user_word_t`.`id`=' . $this->word_id,
            '`user_source_t`.`user_id` = 1'
        ];
        $where->build();

        $col = new YmAdvSqlBuilder('YmSubColBuilder');
        $col->add('SELECT select_user_source_fullpath((`user_source_t`.`id`), ' . $this->user_id . ',2,0)', '(', 'value');
        $col->add('`user_source_t`.`id`', '', 'id');


        $this->sql = <<<SQL
SELECT {$col}
FROM user_word_t
    {$join->out_sql}
    {$where->out_sql}


SQL;
        $this->ymsql->ym_query($this->sql);
//        $item = [
//            'field' => 'user_source',
//            'item_type' => 'single',
//            'field_name' => '用户来源',
//            'field_value' => [],
//            'debug'=>$this->sql
//        ];
//        foreach ($this->ymsql->value as $row) {
//            array_push($item['field_value'], [
//                'value' => $row['source'],
//                'id' => $row['id'],
//            ]);
//        }
//        array_push($this->result_list, $item);
        $this->pushResult('user_source', 'single', '用户来源', '', '', 'value', 'id');
    }

    function getUserTag()
    {
        $join = new YmSqlJoinBuilder();
        $join->list = [
            'user_tag_t',
            'user_tag_dict_t',
        ];
        $join->list2 = [
            'user_tag_t.user_word_id = user_word_t.id',
            'user_tag_dict_t.id = user_tag_t.id',
        ];
        $join->build();

        $where = new YmSqlWhereBuilder(' AND ');
        $where->list = [
            '`user_word_t`.`id`=' . $this->word_id,
            '`user_tag_t`.`user_id` = 1'
        ];
        $where->build();

        $col = new YmAdvColBuilder();
        $col->add("IFNULL(`user_tag_dict_t`.`value`, '无')", '', 'value');
        $col->add('`user_tag_t`.`key_id`', '', 'id');

        $this->sql = <<<SQL
SELECT {$col} FROM user_word_t 
{$join->out_sql}
{$where->out_sql}
SQL;
        $this->ymsql->ym_query($this->sql);
        $this->pushResult('user_tag', 'single', '用户标签', '空', '', 'value', 'id');
    }

    function getBaiduDict()
    {
        $col = new YmAdvColBuilder();
        $col->add("CONCAT(`dict_part_t`.`value`,`dict_sense_t`.`value`)", '', 'value');
        $col->add('`dict_t`.`key_id`', '', 'id');

        $this->sql = <<<SQL
SELECT {$col}
FROM `dict_t`
       LEFT JOIN dict_part_t ON dict_part_t.id = dict_t.part_id
       LEFT JOIN dict_sense_t ON dict_sense_t.id = dict_t.sense_id
       LEFT JOIN dict_lang_t ON dict_lang_t.id = dict_t.lang_id
WHERE html_id = 2 AND `word_value` = '{$this->word}'
ORDER BY `dict_part_t`.`value`  
SQL;
        $this->ymsql->ym_query($this->sql);
        $this->pushResult('dict_baidu', 'single', '百度翻译', '', '', 'value', 'id');
    }

    function getHaiciDict()
    {
        $col = new YmAdvColBuilder();
        $col->add("CONCAT(`dict_part_t`.`value`,`dict_sense_t`.`value`)", '', 'value');
        $col->add('`dict_t`.`key_id`', '', 'id');

        $this->sql = <<<SQL
SELECT {$col}
FROM `dict_t`
       LEFT JOIN dict_part_t ON dict_part_t.id = dict_t.part_id
       LEFT JOIN dict_sense_t ON dict_sense_t.id = dict_t.sense_id
       LEFT JOIN dict_lang_t ON dict_lang_t.id = dict_t.lang_id
WHERE html_id = 1 AND `word_value` = '{$this->word}'
ORDER BY `dict_part_t`.`value` ASC 
SQL;
        $this->ymsql->ym_query($this->sql);
        $this->pushResult('dict_haici', 'single', '海词翻译', '', '', 'value', 'id');
    }

    function getHaiciSenseFreq()
    {
        $col = new YmAdvColBuilder();
        $col->add("CONCAT(`haici_freq_t`.`sense`,`haici_freq_t`.`percent`,'%')", '', 'value');
        $col->add('`haici_freq_t`.`id`', '', 'id');

        $this->sql = <<<SQL
SELECT {$col}
FROM `haici_freq_t`
WHERE `word_value` = '{$this->word}' AND type_id=0
ORDER BY `haici_freq_t`.`percent` DESC 
SQL;
        $this->ymsql->ym_query($this->sql);

        $this->pushResult('haici_freq_sense', 'single', '海词翻译词频', '', '', 'value', 'id');
    }

    function getHaiciPartFreq()
    {
        $col = new YmAdvColBuilder();
        $col->add("CONCAT(`haici_freq_t`.`sense`,'-',`haici_freq_t`.`percent`,'%')", '', 'value');
        $col->add('`haici_freq_t`.`id`', '', 'id');

        $this->sql = <<<SQL
SELECT {$col} 
FROM `haici_freq_t`
WHERE `word_value` = '{$this->word}' AND type_id=1
ORDER BY `haici_freq_t`.`percent` DESC 
SQL;
        $this->ymsql->ym_query($this->sql);
        $this->pushResult('haici_freq_sense', 'multi', '海词词性词频', '', $this->sql, 'value', 'id');
    }

    function getGlobExamTag()
    {
        $col = new YmAdvColBuilder();
        $col->add("`glob_tag_dict_t`.`value`", '', 'value');
        $col->add('`glob_tag_t`.`key_id`', '', 'id');

        $this->sql = <<<SQL
SELECT {$col} 
FROM glob_tag_t
LEFT JOIN glob_tag_dict_t ON glob_tag_dict_t.id = glob_tag_t.id
WHERE word_value = '{$this->word}' AND type_id = 1
SQL;

        $this->ymsql->ym_query($this->sql);
        $this->pushResult('glob_tag_exam', 'single', '考试标签', '', '', 'value', 'id');
    }

    function getGlobLevelTag()
    {
        $col = new YmAdvColBuilder();
        $col->add("`glob_tag_dict_t`.`value`", '', 'value');
        $col->add('`glob_tag_t`.`key_id`', '', 'id');

        $this->sql = <<<SQL
SELECT {$col} 
FROM glob_tag_t
LEFT JOIN glob_tag_dict_t ON glob_tag_dict_t.id = glob_tag_t.id
WHERE word_value = '{$this->word}'AND type_id = 0
SQL;
        $this->ymsql->ym_query($this->sql);
        $this->pushResult('glob_tag_level', 'multi', '难度标签', '', '', 'value', 'id');
    }

    function getUserDict()
    {
        $this->sql = <<<SQL
SELECT `user_dict_value`  as 'value' FROM `user_word_t` 
WHERE `value` = '{$this->word}' AND user_id = 1
SQL;
        $item = [
            'field' => 'user_dict',
            'item_type' => 'single',
            'field_name' => '用户释义',
            'field_value' => []
        ];
        $this->ymsql->ym_query($this->sql);
        foreach ($this->ymsql->value as $row) {
            array_push($item['field_value'], $row['value']);
        }
        array_push($this->result_list, $item);
    }

    function getUserEg()
    {
        $col = new YmAdvColBuilder();
        $col->add('`key_id`', '', 'value');
        $col->add('`user_source_id`');
        $col->add('`eng_eg`');
        $col->add('`eng_word`');
        $col->add('`chi_eg`');
        $col->add('`chi_word`');
        $where = new YmSqlWhereBuilder(' AND ');
        $where->list = [
            '`user_id`=' . $this->user_id,
            '`user_word_id`=' . $this->word_id,
        ];
//
        $this->sql = <<<SQL
SELECT {$col}
FROM user_eg_t
{$where}
SQL;
        $this->ymsql->ym_query($this->sql);
        $this->pushResult('user_eg', 'single', '用户例句', '', '', 'user_source_id', 'eng_eg', 'eng_word', 'chi_eg', 'chi_word');
    }
}