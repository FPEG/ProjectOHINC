<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2019/6/2
 * Time: 0:00
 */
include_once 'YmMysql.php';
include_once "YmSqlListBuilder.php";
include_once 'YmWordProperty.php';
//export XDEBUG_CONFIG="idekey=session_name remote_host=localhost profiler_enable=1";
//phpinfo();
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
//var_dump('asd');
//require_once(‘abc.php’);
$return_data = [];
$ymsql = new YmOhincMysql("localhost", "root", "AAdmin5!>", "ProjectOHINC_db");
$sql = '';
$glob_user_id = 1;
$null_tag = 1;
//$value = $_POST['value'];
switch ($_POST['action']) {
    case 'select':
        switch ($_POST['type']) {
            case 'all_user_source':
                $col = new YmSqlColBuilderS();
                $col->list = ['value', 'id', 'parent_id'];
                $col->build();

                $where = new YmSqlWhereBuilder();
                $where->list = ['user_id=' . $glob_user_id];
                $where->build();

                $sql = <<<SQL
SELECT {$col->out_sql} from user_source_dict_t {$where->out_sql}
SQL;
                $ymsql->ym_query($sql);
                break;
            case 'all_user_tag':
                $col = new YmSqlColBuilderS();
                $col->list = ['value', 'id'];
                $col->build();

                $where = new YmSqlWhereBuilder();
                $where->list = ['user_id=' . $glob_user_id];
                $where->build();

                $sql = <<<SQL
SELECT {$col->out_sql} from user_tag_dict_t {$where->out_sql}
SQL;
                $ymsql->ym_query($sql);
                break;
            case 'user_tag':
                $input_value = $_POST['value'];
                //------------------col
                $col = new YmSqlListBuilder('', ',', '', '');
                $col->list = [
                    'user_word_t.`value` as `word`',
//                    'IFNULL(user_tag_dict_t.value, \'无\') as `tag`',
                    'group_concat(DISTINCT select_user_source_fullpath((user_source_t.id),' .
                    $glob_user_id
                    . ',-1,10)) as `source`',
                ];
                $col->build();
                //------------------join
                $join = new YmSqlJoinBuilder();
                $join->list = [
                    'user_tag_t',
                    'user_tag_dict_t',
                    'user_source_t',
                    'user_source_dict_t',
                ];
                $join->list2 = [
                    'user_word_t.id = user_tag_t.user_word_id',
                    'user_tag_t.id = user_tag_dict_t.id',
                    'user_word_t.id = user_source_t.user_word_id',
                    'user_source_t.id = user_source_dict_t.id',
                ];
                $join->build();
                //------------------where
                $where = new YmSqlWhereBuilder();
//                array_push($where->list, 'user_tag_t.user_id =' . $glob_user_id);
                //处理“无标签”
                if ($input_value['id'] == $null_tag) {
                    array_push($where->list, 'user_tag_t.id is null');
                } else {
                    array_push($where->list, 'user_tag_t.id = ' . $input_value['id']);
                }
                $where->build();

                $sql = <<<SQL
SELECT {$col->out_sql} FROM `user_word_t` {$join->out_sql} {$where->out_sql}
GROUP BY `word`
SQL;
                $ymsql->ym_query($sql);
                break;
            case 'user_source':
                $input_value = $_POST['value'];
                $col = new YmSqlListBuilder('', ',', '', '');
                $col->list = [
                    'user_word_t.`value` as `word`',
                    'IFNULL(user_tag_dict_t.value, \'无\') as `tag`',
//                    'IFNULL(user_source_dict_t.value, \'无\') as `source`',
                ];
                $col->build();
                $join = new YmSqlJoinBuilder();
                $join->list = [
                    'user_tag_t',
                    'user_tag_dict_t',
                    'user_source_t',
                    'user_source_dict_t',
                ];
                $join->list2 = [
                    'user_word_t.id = user_tag_t.user_word_id',
                    'user_tag_t.id = user_tag_dict_t.id',
                    'user_word_t.id = user_source_t.user_word_id',
                    'user_source_t.id = user_source_dict_t.id',
                ];
                $join->build();
                $where = new YmSqlWhereBuilder(' AND ');
                $where->list = [
                    'user_source_t.user_id =' . $glob_user_id,
                    'user_source_t.id = ' . $input_value['id']
                ];
                $where->build();

                $sql = <<<SQL
SELECT {$col->out_sql} FROM `user_word_t` {$join->out_sql} {$where->out_sql}
ORDER BY user_source_t.key_id DESC
SQL;
                $ymsql->ym_query($sql);
                break;
            case 'user_source_fullpath':
                $input_value = $_POST['value'];
                $sql = <<<SQL
SELECT select_user_source_fullpath({$input_value['id']},{$glob_user_id},-1,0) as 'value'
SQL;
                $ymsql->ym_query($sql);
                break;
            case 'word':
                $input_value = $_POST['value'];
                //Colbuilder
                $col = new YmSqlListBuilder('', ',', '', '');
                $col->list = [
                    'user_word_t.`value` as `word`',
                    'IFNULL(group_concat(DISTINCT user_tag_dict_t.value), \'无\') as `tag`',
                    'group_concat(DISTINCT select_user_source_fullpath((user_source_t.id),' .
                    $glob_user_id
                    . ',0,0)) as `source`',
                ];
                $col->build();
                $join = new YmSqlJoinBuilder();
                $join->list = [
                    'user_tag_t',
                    'user_tag_dict_t',
                    'user_source_t',
                    'user_source_dict_t',
                ];
                $join->list2 = [
                    'user_word_t.id = user_tag_t.user_word_id',
                    'user_tag_t.id = user_tag_dict_t.id',
                    'user_word_t.id = user_source_t.user_word_id',
                    'user_source_t.id = user_source_dict_t.id',
                ];
                $join->build();
                $where = new YmSqlWhereBuilder(' OR ');
                foreach ($input_value as $word) {
                    array_push($where->list, 'user_word_t.`value` like \'' . $word . '%\'');
                }
                $where->build();

                $sql = <<<SQL
SELECT {$col->out_sql} FROM `user_word_t` {$join->out_sql} {$where->out_sql}
GROUP BY `word`
SQL;
                $ymsql->ym_query($sql);
                break;
        }
        $return_data['sql'] = $sql;
        $return_data['status'] = $ymsql->status;
        $return_data['value'] = $ymsql->value;
        break;
    case 'insert':
        switch ($_POST['type']) {
            case 'user_tag':
                $input_value = $_POST['value'];
                $col = new YmSqlColBuilderI();
                $col->list = ['user_id', 'value'];
                $col->build();
                $value = new YmSqlValueBuilder();
                $value->list = [
                    $glob_user_id,
                    $input_value['value_text']
                ];
                $value->build();
                $sql = <<<SQL
INSERT INTO `user_tag_dict_t` {$col->out_sql}{$value->out_sql}
SQL;
                $ymsql->ym_query($sql);
                break;
            case 'user_source':
                $input_value_json = $_POST['value'];
                $col = new YmSqlColBuilderI();
                $col->list = ['user_id', 'value', 'parent_id'];
                $col->build();
                $value = new YmSqlValueBuilder();
                $value->list = [
                    $glob_user_id,
                    $input_value_json['value_text'],
                    $input_value_json['id']];
                $value->build();
                $sql = <<<SQL
INSERT INTO `user_source_dict_t` {$col->out_sql}{$value->out_sql}
SQL;
                $ymsql->ym_query($sql);
                $return_data['sql'] = $sql;
                break;
            case 'word':
                $input_value_json = $_POST['value'];

                $col = new YmSqlColBuilderI();
                $value = new YmSqlValueBuilder();
                //循环插入到user_word_t
                foreach ($input_value_json['word'] as $word) {
                    $col->list = ['user_id', 'value'];
                    $col->build();
                    $value->list = [
                        $glob_user_id,
                        $word];
                    $value->build();
                    $sql = <<<SQL
INSERT INTO `user_word_t` {$col->out_sql}{$value->out_sql}
SQL;
                    $ymsql->ym_query($sql);
//循环插入到user_tag_t
                    $col->list = ['user_id', 'user_word_id', 'id'];
                    $col->build();
                    if ($input_value_json['user_tag']['id'] != $null_tag) {
                        $sql = <<<SQL
INSERT INTO `user_tag_t` {$col->out_sql} VALUE (
{$glob_user_id},
(SELECT `id` FROM `user_word_t` where `value` = '{$word}'),
{$input_value_json['user_tag']['id']}
)
SQL;
                        $ymsql->ym_query($sql);
                    }

//循环插入到user_source_t
                    $sql = <<<SQL
INSERT INTO `user_source_t` {$col->out_sql} VALUE (
{$glob_user_id},
(SELECT `id` FROM `user_word_t` where `value` = '{$word}'),
{$input_value_json['user_source']['id']}
)
SQL;
                    $ymsql->ym_query($sql);
                    $sql = '';
                }
                break;
            case 'python_search':
                $word_list = $_POST['word'];
                $str = $word_list[0];
                for ($i = 1; $i < count($word_list); $i++) {
                    $str .= '  ' . $word_list[$i];
                }
                $console_out = exec('sudo /root/anaconda3/bin/python /var/python_project/ProjectOHINC/main.py --word_list "' . $str . '" --user_id ' . $glob_user_id);
                $return_data['$console_out'] = $console_out;
                break;
            case 'python_analyze':
                $word_list = $_POST['word'];
                $str = $word_list[0];
                for ($i = 1; $i < count($word_list); $i++) {
                    $str .= '  ' . $word_list[$i];
                }
                $console_out = exec('sudo /root/anaconda3/bin/python /var/python_project/ProjectOHINC/main.py --action analyze --word_list "' . $str . '" --user_id ' . $glob_user_id);
                $return_data['$console_out'] = $console_out;
                break;
            case 'analyze_result':
                $json_str = file_get_contents('/var/python_project/ProjectOHINC/analyze_result_' . $glob_user_id . '.json');
                $json_str = str_replace('"pos"', '"sense"', $json_str);
                $json_list = json_decode($json_str, true);
                foreach ($json_list as $json_item) {
                    if (is_array($json_item['value']) && !empty($json_item['value'])) {
                        foreach ($json_item['value'] as $value_item) {
                            switch ($value_item['table']) {
                                case 'haici_freq_t':
                                    $col = new YmSqlColBuilderI();
                                    $valueb = new YmSqlValueBuilder();
                                    array_push($col->list, 'word_value');
                                    array_push($valueb->list, $json_item['word']);
                                    foreach ($value_item['cols'] as $key => $value) {
                                        array_push($col->list, $key);
                                        array_push($valueb->list, $value);
                                    }
                                    $col->build();
                                    $valueb->build();
                                    $sql = <<<SQL
INSERT INTO `haici_freq_t` {$col->out_sql}{$valueb->out_sql}
SQL;
                                    $return_data['debug1'] = $sql;
                                    $ymsql->ym_query($sql);
                                    break;
                                case 'glob_tag_t':
                                    $sql = <<<SQL
INSERT IGNORE INTO `glob_tag_dict_t`(`value`, type_id) VALUE ('{$value_item['cols']['value']}',{$value_item['cols']['type_id']})
SQL;
                                    $ymsql->ym_query($sql);

                                    $col = new YmSqlColBuilderI();
                                    $col->list = ['word_value', 'id'];
                                    $col->build();
                                    $value = new YmSqlValueBuilder('');
                                    $temp_sql = <<<SQL
(SELECT `id` FROM `glob_tag_dict_t` 
WHERE `value` = '{$value_item['cols']['value']}' AND `type_id` = {$value_item['cols']['type_id']})
SQL;
                                    $value->list = ['\'' . $json_item['word'] . '\'', $temp_sql];
                                    $value->build();
                                    $sql = <<<SQL
INSERT INTO `glob_tag_t` {$col->out_sql}{$value->out_sql}
SQL;
                                    $ymsql->ym_query($sql);
                                    break;
                                case 'dict_t':
                                    $msql = <<<SQL
INSERT IGNORE INTO `dict_lang_t`(`value`) VALUE('{$value_item['cols']['lang']}');
INSERT IGNORE INTO `dict_part_t`(`value`) VALUE('{$value_item['cols']['part']}');
INSERT IGNORE INTO `dict_sense_t`(`value`) VALUE('{$value_item['cols']['sense']}');
INSERT IGNORE INTO `dict_t` (word_value, sense_id, part_id, lang_id, html_id) VALUE (
'{$json_item['word']}',
(SELECT `id` FROM `dict_sense_t` WHERE `value` = '{$value_item['cols']['sense']}'),
(SELECT `id` FROM `dict_part_t` WHERE `value` = '{$value_item['cols']['part']}'),
(SELECT `id` FROM `dict_lang_t` WHERE `value` = '{$value_item['cols']['lang']}'),
(SELECT `id` FROM `html_name_t` WHERE `value` = '{$value_item['cols']['html_name']}')
);
SQL;
                                    $ymsql->ym_multi_query($msql);
                                    break;
                            }
                        }
                    }
                    //插入四六级
                    $sql = <<<SQL
CALL insert_cet('{$json_item['word']}')
SQL;
                    $ymsql->ym_query($sql);
                }

                $return_data['debug'] = $json_list;
                break;
            case 'user_dict':
                $where = new YmSqlWhereBuilder();
                $where->list = [
                    '`value` = \'' . $_POST['word'] . '\'',
                    '`user_id`=' . $glob_user_id,
                ];
                $where->build();
                $sql = <<<SQL
UPDATE `user_word_t` SET `user_dict_value` = '{$_POST['value']}' 
{$where->out_sql}
SQL;
                $ymsql->ym_query($sql);

                break;
            case 'user_eg':
                $input_value_json = $_POST['value'];
                $col = new YmSqlColBuilderI();
                $col->list = [
                    'user_id',
                    'user_word_id',
                    'user_source_id',
                    'eng_eg',
                    'eng_word',
                    'chi_eg',
                    'chi_word'];
                $value = new YmSqlValueBuilder();
                $value->list = [
                    $glob_user_id,
                    $input_value_json['user_word_id'],
                    $input_value_json['user_source_id'],
                    $input_value_json['eng_eg'],
                    $input_value_json['eng_word'],
                    $input_value_json['chi_eg'],
                    $input_value_json['chi_word'],
                ];
                if ($input_value_json['key_id']) {
                    array_push($value->list, $input_value_json['key_id']);
                    array_push($col->list, 'key_id');
                }

                $sql = <<<SQL
REPLACE INTO `user_eg_t` {$col}{$value}
SQL;
                $ymsql->ym_query($sql);
                $return_data['sql'] = $sql;
                break;
            case 'bold':
                $input_value_json = $_POST['value'];
                //todo adv
                $col = new YmSqlColBuilderI();
                $col->list = ['user_id', 'field_name','field_key_id', 'is_bold'];
                $value = new YmSqlValueBuilder('');
                $value->list = [$glob_user_id, $input_value_json['key_id'], $input_value_json['value']];
                $sql = <<<SQL
REPLACE INTO `user_bold_t` {$col}{$value}
SQL;
                $ymsql->ym_query($sql);

                break;
        }
        $return_data['sql'] = $sql;
        $return_data['status'] = $ymsql->status;
        $return_data['value'] = $ymsql->value;
        break;
    case 'word'://选取单个单词
        $word = $_POST['word'];
        $ywp = new YmWordProperty($word);
        $ywp->conUpDown();

        $ywp->getBaiduDict();
        $ywp->getHaiciDict();

        $ywp->getUserDict();
        $ywp->getUserTag();
        $ywp->getGlobLevelTag();
        $ywp->getHaiciPartFreq();
        $ywp->getUserSource();

//        $ywp->getIpa();
        $ywp->getHaiciSenseFreq();
        $ywp->getGlobExamTag();
        $ywp->getUserEg();
        $return_data['value'] = $ywp->result_list;
        break;
    case 'pylog':
        $fp = file('/var/python_project/ProjectOHINC/log-' . (string)$glob_user_id . '.log');
        $return_data['value'] = [];
        $return_data['value']['pylog'] = $fp[count($fp) - 1];
        break;
}

echo json_encode($return_data);