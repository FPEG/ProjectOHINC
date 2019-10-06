var mc;
var ml;
var mr;
var mco;

function ControlTable(selector) {
    this.$selector = $(selector);
    this.all_value = {
        'word': this.get_value('word', 'textarea').split('/'),
        'user_source': {
            'id': this.get_selector('user_source', '.div-input').attr('data-index'),
            'value_text': this.get_value('user_source', 'input')
        },
        'user_tag': {
            'id': this.get_selector('user_tag', 'input').attr('data-index'),
            'value_text': this.get_value('user_tag', 'input')
        },
        // 'user_forget': this.get_value('user_forget', 'input'),
        // 'haici_level': this.get_value('haici_level', 'input'),
    }
}

// ControlTable.prototype.get_input_value = function (field_name) {
//     //获得通用输入val
//     var $subselector = this.$selector.find('tr[data-field="' + field_name + '"]');
//
//     if ($subselector.find('td select').length > 0 && $subselector.find('td input').val() === '') {
//         //存在select
//         return $subselector.find('td select').val()
//     }
//     else {
//         return $subselector.find('td input').val();
//     }
//
//
// };
// ControlTable.prototype.get_select_selector = function (field_name) {
//     var $subselector = this.$selector.find('tr[data-field="' + field_name + '"]');
//     if ($subselector.find('td select').length > 0) {
//         //存在select
//         return $subselector.find('td select')
//     }
//     else {
//         return null;
//     }
//
// };
ControlTable.prototype.get_selector = function (field_name, node_name) {
    var $subselector = this.$selector.find('tr[data-field="' + field_name + '"] td');
    if ($subselector.children(node_name).length > 0) {
        //存在select
        return $subselector.children(node_name)
    } else {
        return null;
    }

};
ControlTable.prototype.get_value = function (field_name, node_name) {
    var $subselector = this.$selector.find('tr[data-field="' + field_name + '"] td');
    return $subselector.find(node_name).val();
};
ControlTable.prototype.get_all_value = function (field = null) {
    var json_out = {
        'word': this.get_value('word', 'textarea').split('/'),
        'user_source': {
            'id': this.get_selector('user_source', '.div-input').attr('data-index'),
            'value_text': this.get_value('user_source', 'input')
        },
        'user_tag': {
            'id': this.get_selector('user_tag', 'input').attr('data-index'),
            'value_text': this.get_value('user_tag', 'input')
        },
    };
    switch (field) {
        case null:
            return json_out;
        default:
            return json_out[field];
    }
}


function MainControl() {
    this.poster = new ThPoster();
    this.main_control_table = new ControlTable('#main__control__table');
    this.post_json = null;
    this.return_json = null;

    this.builder_json = {};
    this.builder_json['select'] = new ThSelectWidgetBuilder();
    this.builder_json['select'].$selector = this.main_control_table.get_selector('user_tag', 'select');

    this.builder_json['menu'] = new ThMenuWidgetBuilder();
    this.builder_json['menu'].$selector = this.main_control_table.get_selector('user_source', 'div.ul');

    this.builder_json['menu'].$selector.on("menuselect", function (event, ui) {
        mc.poster.async = true;
        mc.poster.sent(
            {
                'action': 'select',
                'type': 'user_source_fullpath',
                'value':
                    {
                        'id': ui['item'].children('div').attr('data-index')
                    }
            }, 'action.php', function (return_data) {
                var $div_class = mc.main_control_table.get_selector('user_source', 'div.div-input');
                $div_class.text(return_data['value'][0]['value']);
                $div_class.attr('data-index', ui['item'].children('div').attr('data-index'));
                // mc.main_control_table.get_selector('user_source', 'button.search').click();
            }
        );
        // $div_class.text(ui['item'].children('div').text());
        mc.poster.async = false;

    });
}

MainControl.prototype.build_user_tag_selector = function (first_opt = null) {
    this.post_json = {
        'action': 'select',
        'type': 'all_user_tag',
    };
    this.poster.sent(this.post_json, 'action.php');
    this.return_json = this.poster.return_data['value'];
    this.builder_json['select'].in_json = this.return_json;
    this.builder_json['select'].first_option = first_opt;
    this.builder_json['select'].build();
    this.builder_json['select'].$selector.siblings('span[role]').css("width", this.builder_json['select'].$selector.siblings('input').innerWidth() - 34 + "px");
    var id = this.builder_json['select'].in_json[0]['id'];
    mc.main_control_table.get_selector('user_tag', 'input').attr('data-index', id);

};
MainControl.prototype.build_source_menu = function () {
    this.post_json = {
        'action': 'select',
        'type': 'all_user_source',
    };
    this.poster.sent(this.post_json, 'action.php');
    this.builder_json['menu'].in_json = this.poster.return_data['value'];
    this.builder_json['menu'].build();
};

function MainList() {
    this.json_list = [];
    this.html_builder = new ThListItemHtmlBuilder();
    this.$selector = $('#main__list')
    this.current_word = '';
    this.current_index = 0;
}

MainList.prototype = {
    choose: function (index = 0) {
        // if (!index) {
        //     index = ml.current_index;
        // }
        $('#main__list .list-item').css('background-color', '#EDEDED');
        this.$selector.find('.list-item:eq(' + index + ')').css('background-color', 'white');
        //取得单词名
        var json_item = ml.json_list[index];
        var in_json = {
            'action': 'word',
            'word': json_item['word']
        };
        ml.current_word = json_item['word'];
        ml.current_index = Number(index);
        mc.poster.sent(in_json, 'action.php');
        //接收数据
        mr.word = json_item['word'];
        mr.json_list = mc.poster.return_data['value'];
        //处理数据
        mr.build();
    },
    build: function () {
        this.$selector.html('');
        for (var i = 0; i < this.json_list.length; i++) {

            this.html_builder.word = this.json_list[i]['word'];
            for (var key in this.json_list[i]) {
                if (key !== 'word') {
                    this.html_builder.fields.push(this.json_list[i][key]);
                }
            }
            this.html_builder.index = i;
            this.html_builder.build();
            this.$selector.append(this.html_builder.$dom);
        }
    },
    choose_next: function () {
        if (this.current_index + 1 < this.json_list.length) {
            this.choose(this.current_index + 1)
        }
    },
    choose_back: function () {
        if (this.current_index - 1 > -1) {
            this.choose(this.current_index - 1)
        }
    }
};
var MainConsole = function () {
    this.$selector = $('#main__control__console')
};
MainConsole.prototype.log = function (str) {
    if (str !== this.get_last()) {
        this.$selector.append('<span>' + str + '</span>');
        this.$selector.append('<br>')
    }
    this.scroll_to_buttom();
};
MainConsole.prototype.clear = function () {
    this.$selector.html('');
};
MainConsole.prototype.get_last = function () {
    return this.$selector.find('span:last').text();
}
MainConsole.prototype.scroll_to_buttom = function () {
    var scrollHeight = this.$selector.prop("scrollHeight");
    this.$selector.animate({scrollTop: scrollHeight}, 100);
}


var MainResult = function () {
    this.json_list = [];
    this.$selector = $('#main__result');
    //构造器
    this.normol_builder = new ThNormolResultItemBuilder();
    this.userdict_builder = new ThUserDictResultItemBuilder();
    this.usereg_builder = new ThUserEgResultItemBuilder();
    this.control_updown_builder = new ThControlUpDownResultItemBuilder();
    this.word = ''
};
MainResult.prototype.build = function () {
    this.$selector.html('');
    for (var index = 0; index < this.json_list.length; index++) {
        var json_item = this.json_list[index];
        var builder = null;
        switch (json_item['field']) {
            case 'user_dict':
                builder = this.userdict_builder;
                break;
            case 'user_eg':
                builder = this.usereg_builder;
                break;
            case 'controller_updown':
                builder = this.control_updown_builder;
                break;

            default:
                builder = this.normol_builder;
                break;
        }
        builder.index = index;
        //字段值
        builder.field_value = json_item['field_value'];
        //字段中文
        builder.field_name = json_item['field_name'];
        //字段英文
        builder.field = json_item['field'];
        //项目类型single/muilt
        builder.item_type = json_item['item_type'];

        builder.build();
        this.$selector.append(builder.$dom);

    }
};
MainResult.prototype.find_json = function (field) {
    for (let index = 0; index < this.json_list.length; index++) {
        if (this.json_list[index]['field'] === field) {
            return this.json_list[index]['field_value'];
        }
    }
    return null;
};
