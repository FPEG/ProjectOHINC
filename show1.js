$(function () {
    //测试
    // var twb = new ThMenuWidgetBuilder()
    // var poster1 = new ThPoster()
    // var post_json1 = {
    //     'action': 'select',
    //     'type': 'all_user_source',
    // };
    // poster1.sent(post_json1, 'action.php');
    // twb.in_json = poster1.return_data['value'];
    // twb.$selector = $('.item__value__flow--horizon .ul');
    // twb.build();

    var tsb = new ThSelectWidgetBuilder()
    tsb.$selector = $('.item__value__flow--horizon.pad select');
    tsb.in_json = [{'id': 123, 'value': 'test1'}, {'id': 124, 'value': 'test2'}];
    tsb.width = '100%';
    tsb.build();
    //测试完毕
    mc = new MainControl();
    ml = new MainList();
    mr = new MainResult();
    mco = new MainConsole();
    var get_pylog = function () {
        var poster = new ThPoster();
        poster.async = true;
        poster.sent({
            'action': 'pylog',
            'type': 'pylog',
        }, 'action.php', function (data) {
            mco.log(data['value']['pylog']);
        })
    };
    //选择来源
    //选择标签
    mc.builder_json['select'].$selector.on("selectmenuselect", function (event, ui) {
        var $div_class = mc.main_control_table.get_selector('user_tag', 'select');
        var index = $div_class.children('option:eq(' + ui['item'].index + ')').attr('data-index')
        $div_class = mc.main_control_table.get_selector('user_tag', 'input');
        $div_class.attr('data-index', index)
    });
    $('button').button();
    mc.build_user_tag_selector('无');
    mc.build_source_menu();
    //初始化默认选项
    //搜索按钮
    $(document).on('click', '#main__control__table button.search', function () {
        var my_control_table = new ControlTable('#main__control__table');
        var value = my_control_table.get_all_value($(this).parent().parent().attr('data-field'))
        var poster = new ThPoster();
        var in_json = {
            'action': 'select',
            'word': my_control_table.get_all_value('word'),
            'type': $(this).parent().parent().attr('data-field'),
            'value': value
        };
        poster.sent(in_json, 'action.php');
        ml.json_list = poster.return_data['value'];
        ml.build();
        $('#main__list .list-item[data-index="0"] .item__word').click();
    });
    //插入按钮
    $(document).on('click', '#main__control__table button.insert', function () {
        var my_control_table = new ControlTable('#main__control__table');
        var value = my_control_table.get_all_value($(this).parent().parent().attr('data-field'));
        var poster = new ThPoster();
        var in_json = {
            'action': 'insert',
            'word': my_control_table.get_all_value('word'),
            'type': $(this).parent().parent().attr('data-field'),
            'value': value
        };
        poster.sent(in_json, 'action.php');

        switch ($(this).parent().parent().attr('data-field')) {
            case 'user_tag':
                //改变默认选项，刷新选项
                mc.build_user_tag_selector(my_control_table.get_all_value('user_tag')['value_text']);
                break;
            case 'user_source':
                mc.build_source_menu();
                break;
        }

    });
    //查找新单词
    $(document).on('click', '#main__control__btn--submit', function () {
        var main_control_table = new ControlTable('#main__control__table');
        var in_json;
        // JSON.stringify(json_in)
        // sj(main_control_table.get_all_value());
        in_json = {
            'action': 'insert',
            'type': 'word',
            'value': main_control_table.get_all_value()
        };
        mc.poster.sent(in_json, 'action.php');


        in_json = {
            'word': mc.main_control_table.get_all_value('word'),
            'action': 'insert',
            'type': 'python_search'
        };
        var poster = new ThPoster();
        poster.async = true;
        poster.sent(in_json, 'action.php', function () {
            mco.log('--搜索完成--');
            var in_json = {
                'word': mc.main_control_table.get_all_value('word'),
                'action': 'insert',
                'type': 'python_analyze'
            };
            var poster = new ThPoster();
            poster.async = true;
            poster.sent(in_json, 'action.php', function () {
                mco.log('--分析完成--');
                var in_json = {
                    'action': 'insert',
                    'type': 'analyze_result'
                };
                var poster = new ThPoster();
                poster.async = true;
                poster.sent(in_json, 'action.php', function () {
                    mco.log('--上传分析--')
                });
            });
        });
    });
    //只插入
    $(document).on('click', '#main__control__btn--insert-only', function () {
        var main_control_table = new ControlTable('#main__control__table');
        var in_json;
        // JSON.stringify(json_in)
        // sj(main_control_table.get_all_value());
        in_json = {
            'action': 'insert',
            'type': 'word',
            'value': main_control_table.get_all_value()
        };
        mc.poster.sent(in_json, 'action.php');
        main_control_table.get_selector('user_source', 'button.search').click();
    });
    //只搜索
    $(document).on('click', '#main__control__btn--search-only', function () {
        mco.clear();
        var main_control_table = new ControlTable('#main__control__table');
        var in_json;
        var word_in = mc.main_control_table.get_all_value('word');

        var log_timer = setInterval(get_pylog, 4500);

        in_json = {
            'word': word_in,
            'action': 'insert',
            'type': 'python_search'
        };
        var poster = new ThPoster();
        poster.async = true;
        poster.sent(in_json, 'action.php', function () {
            mco.log('--搜索完成--');
            clearInterval(log_timer);
            var in_json = {
                'word': word_in,
                'action': 'insert',
                'type': 'python_analyze'
            };
            var poster = new ThPoster();
            poster.async = true;
            poster.sent(in_json, 'action.php', function () {
                mco.log('--分析完成--');
                var in_json = {
                    'action': 'insert',
                    'type': 'analyze_result'
                };
                var poster = new ThPoster();
                poster.async = true;
                poster.sent(in_json, 'action.php', function () {
                    mco.log('--上传分析--')
                });
            });
        });
    });
    //单词列表的单词---------------------------------------------------------------------------------------------------------
    $('#main__list').on('click', '.item__word', function () {
        //取得本地列表编号
        var index = $(this).parent().parent().parent().parent().parent().attr('data-index');
        ml.choose(index);
    });
    //用户释义提交
    $(document).on('click', '#main__result__user-dict', function () {
        mc.poster.sent({
            'action': 'insert',
            'type': 'user_dict',
            'word': mr.word,
            'value': $(this).siblings('textarea').val(),
        }, 'action.php')
    });
    //单词输入整理
    mc.main_control_table.get_selector('word', 'textarea').on('input', function () {
        var text = $(this).val();
        $(this).val(text.replace(/ {2}/g, '/'));
        text = $(this).val();
        $(this).val(text.replace(/\n/g, ''));
        text = $(this).val();
        $(this).val(text.toLowerCase());
    })
    //海词词频高亮
    $(document).on('click', '.result-item.single[data-field="haici_freq_sense"]', function (event) {
        if ($(event.target).attr('class') === 'item__value__tag boldness') {
            $(event.target).removeClass('boldness');
            $(event.target).addClass('bold');
            let in_json = {
                'action':'insert',
                'type':'haici_freq_tran_bold',
                'value':{
                    'key_id':$(event.target).attr('data-index'),
                    'value':1
                }
            };
            mc.poster.sent(in_json,'action.php');
            // alert($(event.target).attr('data-index'));
        }
        else if ($(event.target).attr('class') === 'item__value__tag bold') {
            $(event.target).removeClass('bold');
            $(event.target).addClass('boldness');
            let in_json = {
                'action':'insert',
                'type':'haici_freq_tran_bold',
                'value':{
                    'key_id':$(event.target).attr('data-index'),
                    'value':0
                }
            };
            mc.poster.sent(in_json,'action.php');
            // alert($(event.target).attr('data-index'));
        }

    })
});