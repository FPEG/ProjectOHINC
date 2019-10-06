function ThPoster() {
    this.async = false;
    this.return_data = [];
}

ThPoster.prototype.sent = function (in_json, url, callback = undefined) {
    if (this.async === true) {
        //异步执行
        //要求回调函数
        if (callback !== undefined) {
            $.ajax({
                dataType: 'json',
                url: url,
                data: in_json,
                type: "POST",
                async: true,
                success: callback
            });
        } else {
            alert("没有回调函数，post失败")
        }
    } else {
        //同步执行
        //结果保存到return_data
        var rdata;
        $.ajax({
            dataType: 'json',
            url: url,
            data: in_json,
            type: "POST",
            async: false,//这里选择异步为false，那么这个程序执行到这里的时候会暂停，等待
                         //数据加载完成后才继续执行
            success: function (data) {
                rdata = data;
            }
        });

        this.return_data = rdata;
        //判断错误
        if (this.return_data !== undefined) {
            if (this.return_data['status'] === 1) {
                // alert(this.return_data['value']);
                // alert(this.return_data['sql'])
                this.return_data = null
            }
        }

    }
};

function sj(json_in) {
    var ret = JSON.stringify(json_in);
    alert(ret);
    return ret
}

var ThBaseWidgetBuilder = function () {
    this.$selector = undefined;
    this.$div_class = undefined;
    this.$dom = undefined;
    this.in_json = [];
};

ThBaseWidgetBuilder.prototype.build = function () {

};
ThBaseWidgetBuilder.prototype.build_dom = function () {

};

var ThSelectWidgetBuilder = function () {
    ThBaseWidgetBuilder.call(this);
    this.width = 'auto';
    this.option_list = [];
    this.option_width = null;
    this.first_option = null;
};
//in_json:value,id
ThSelectWidgetBuilder.prototype = new ThBaseWidgetBuilder();
ThSelectWidgetBuilder.prototype.build = function () {
    if (this.$selector.selectmenu('instance') !== undefined) {
        this.$selector.selectmenu('destroy')
    }
    this.$selector.html('');
    this.build_dom();
    // this.generate_options();
    // this.$selector.append(generate_options(list_in_raw, first_element));
    this.$selector.selectmenu({
        width: 200
    });
    // alert(this.$selector.selectmenu( "option", "width" ))
    // this.$selector.selectmenu("option", "width", this.option_width);
    if (this.width === 'auto') {
        // this.$selector.siblings('span[role]').css("width", 0 + "px");
        this.$selector.siblings('span[role]').css("width", 'auto');
        // alert(this.$selector.siblings('input').innerWidth());
        // this.$selector.siblings('span[role]').css("width", this.$selector.siblings('input').innerWidth() - 34 + "px");
    } else {
        this.$selector.siblings('span[role]').css("width", this.width);
    }

};
ThSelectWidgetBuilder.prototype.build_dom = function () {
    if (this.first_option !== null) {
        for (var i = 0; i < this.in_json.length; i++) {
            if (this.in_json[i]['value'] === this.first_option) {
                var temp;
                temp = this.in_json[i];
                this.in_json[i] = this.in_json[0];
                this.in_json[0] = temp;
                break;
            }
        }

    }
    var rts = '';
    for (var li in this.in_json) {
        rts += '<option data-index="' + this.in_json[li]['id'] + '">' + this.in_json[li]['value'] + '</option>';
    }
    this.$selector.append(rts);

};

function ThMenuWidgetBuilder() {
    ThBaseWidgetBuilder.call(this);
}

ThMenuWidgetBuilder.prototype = new ThBaseWidgetBuilder();
ThMenuWidgetBuilder.prototype.build_dom = function () {
    this.$dom = $("<ul></ul>");
    // this.$selector.html("<ul></ul>");
    //找到根节点
    var root_id;
    var root_index;
    for (root_index = 0; root_index < this.in_json.length; root_index++) {//找到root_index的位置，一般在第一个
        if (this.in_json[root_index]['parent_id'] === this.in_json[root_index]['id']) {
            root_id = this.in_json[root_index]['id'];//找到起始id
            break;
        }
    }
    this.$dom.append(
        '<li><div data-index="' + this.in_json[root_index]['id'] + '">' + this.in_json[root_index]['value'] + '</div><ul></ul></li>'
    );

    for (var index1 = 0; index1 < this.in_json.length; index1++) {
        // alert(index0)
        if (this.in_json[index1]['parent_id'] === root_id && this.in_json[index1]['id'] !== root_id) {

            this.$dom.children('li').children('ul').append(
                '<li><div data-index="' + this.in_json[index1]['id'] + '">' + this.in_json[index1]['value'] + '</div></li>'
            )
        }
    }
    var root_id_num = Number(root_id);
    for (var o = root_id_num + 1; o < root_id_num + 100; o++) {//先遍历所有parent_id
        var i = String(o);
        for (var index2 = 0; index2 < this.in_json.length; index2++) {//在数组里搜索parent_id = i
            if (this.in_json[index2]['parent_id'] === i) {//在数组里搜索parent_id = i
                for (var index3 = 0; index3 < this.in_json.length; index3++) {//在数组里往前搜索id = i，（要添加子项的目标id）

                    if (this.in_json[index3]['id'] === i) {//在数组里搜索id = i
                        //添加子项
                        if (this.$dom.find('div[data-index="' + this.in_json[index3]['id'] + '"]').siblings('ul').length === 0) {
                            this.$dom.find('div[data-index="' + this.in_json[index3]['id'] + '"]').parent().append(
                                "<ul></ul>"
                            );
                        }
                        this.$dom.find('div[data-index="' + this.in_json[index3]['id'] + '"]').siblings('ul').append(
                            '<li><div data-index="' + this.in_json[index2]['id'] + '">' + this.in_json[index2]['value'] + '</div></li>'
                        )


                    } else if (Number(this.in_json[index3]['id']) > i) {
                        break;
                    }
                }
            }
        }
    }


};
ThMenuWidgetBuilder.prototype.build = function () {
    this.build_dom();
    if (this.$selector.children('ul').menu('instance') !== undefined) {
        this.$selector.children('ul').menu('destroy')
    }
    this.$selector.html(this.$dom);
    this.$selector.children('ul').menu();
};

function ThBaseHtmlBuilder() {
    this.$dom = null;
    this.html = '';
}


function ThListItemHtmlBuilder() {
    ThBaseHtmlBuilder.call(this);
    this.index = 0;
    this.word = '';
    this.fields = [];
}

ThListItemHtmlBuilder.prototype.build = function () {
    this.$dom = $('<div class="list-item" data-index="' + this.index + '"><table><tr></tr></table></div>');
    var item_word = '<td><div class="item__word" >' + this.word + '</div></td>';
    this.$dom.find('tr').append(item_word);
    for (var index = 0; index < this.fields.length; index++) {
        var spliter = '<td class="spliter--table"></td>';
        var item_field = '<td><div class="item__field">' + this.fields[index] + '</div></td>';
        this.$dom.find('tr').append(spliter);
        this.$dom.find('tr').append(item_field)
    }
    this.fields = [];
};

function ThBaseResultItemBuilder() {
    ThBaseHtmlBuilder.call(this);
    this.index = 0;
    this.field_name = '';
    this.field = '';
    this.item_type = null;
    this.field_value = [];
}

ThBaseResultItemBuilder.prototype.build = function () {
    //构建名称，index-data
    this.$dom = $('<div class="result-item" data-field="' + this.field + '" data-index="' + this.index + '"><table><tr></tr></table></div>');
    this.$dom.addClass(this.item_type);
    var item_word = '<td><div class="item__field" >' + this.field_name + '</div></td>';
    var spliter = '<td class="spliter--table"></td>';
    var item_value = '<td class="item__value--td"><div class="padding-container"><div class="item__value" ></div></div></td>';
    this.$dom.find('tr').append(item_word);
    this.$dom.find('tr').append(spliter);
    this.$dom.find('tr').append(item_value);
    this.sub_build();
};
ThBaseResultItemBuilder.prototype.sub_build = function () {

};

var ThNormolResultItemBuilder = function () {
    ThBaseResultItemBuilder.call(this);
};

ThNormolResultItemBuilder.prototype = new ThBaseResultItemBuilder();

ThNormolResultItemBuilder.prototype.sub_build = function () {
    // if (this.field_value.length === 1) {
    //     this.$dom.find('.item__value').html(this.field_value[0]['value']);
    // } else {
    //     for (var index = 0; index < this.field_value.length; index++) {
    //         var item_field = '<div class="item__value__tag">' + this.field_value[index]['value'] + '</div>';
    //         this.$dom.find('.item__value').append(item_field)
    //     }
    // }
    for (var index = 0; index < this.field_value.length; index++) {
        var item_field = '<div class="item__value__tag boldness" data-index="' + this.field_value[index]['id'] + '">' + this.field_value[index]['value'] + '</div>';
        this.$dom.find('.item__value').append(item_field)
    }
    if (this.field_value.length === 1 && this.field === 'word') {
        this.$dom.find('.item__value').html(this.field_value[0]['value']);
    }
};

var ThUserDictResultItemBuilder = function () {
    ThBaseResultItemBuilder.call(this);
};
ThUserDictResultItemBuilder.prototype = new ThBaseResultItemBuilder();
ThUserDictResultItemBuilder.prototype.sub_build = function () {
    this.$dom.find('.item__value').append('<textarea>asd</textarea>');
    this.$dom.find('.item__value textarea').val(this.field_value[0]);
    this.$dom.find('.item__value').append('<button id="main__result__user-dict">提交</button>');
    this.$dom.find('.item__value button').button();

};

var ThUserEgResultItemBuilder = function () {
    ThBaseResultItemBuilder.call(this);
    this.builder_list = [];
};
ThUserEgResultItemBuilder.prototype = new ThBaseResultItemBuilder();
ThUserEgResultItemBuilder.prototype.sub_build = function () {
    this.builder_list = [];
    this.$dom.find('.padding-container').html('');
    //按钮绑定----------------------------------------------------------------------------------------------------------------------
    this.$dom.on('click', '.item__value', function (event) {
        let index = $(this).attr('data-index');
        let builder;
        builder = mr.usereg_builder.builder_list[index];
        if ($(event.target).hasClass('item__value__eg-edit-btn')) {
            builder.build_edit();
            $(this).replaceWith(builder.$dom_edit);
        }
        if ($(event.target).hasClass('item__value__eg-cancel-btn')) {
            builder.build_show();
            $(this).replaceWith(builder.$dom_show);
        }
        //提交按钮
        if ($(event.target).hasClass('item__value__eg-submit-btn')) {
            builder.get_val();
            let user_word_id = mr.find_json('word')[0]['id'];
            let in_json = {
                'action': 'insert',
                'type': 'user_eg',
                'value':
                    {
                        'eng_eg': builder.eng_eg,
                        'eng_word': builder.eng_word,
                        'chi_eg': builder.chi_eg,
                        'chi_word': builder.chi_word,
                        'key_id': builder.key_id,//默认0
                        'user_word_id': user_word_id,
                        'user_source_id': builder.user_source_id
                    }
            };
            // sj(in_json);
            mc.poster.sent(in_json, 'action.php');
            builder.build_show();
            $(this).replaceWith(builder.$dom_show);
            if (builder.key_id === 0) {
                // mr.usereg_builder.build();
                ml.choose();
            }
        }
    });
    //选择绑定----------------------------------------------------------------------------------------------------------------------
    this.$dom.on("selectmenuselect", '.item__value', function (event, ui) {
        let index1 = $(this).attr('data-index');
        let builder;
        builder = mr.usereg_builder.builder_list[index1];
        var $div_class = builder.$dom_edit.find('select');
        builder.user_source_id = $div_class.children('option:eq(' + ui['item'].index + ')').attr('data-index');
        // alert(builder.user_source_id);
    });
    //构建其他例句
    for (let i = 0; i < this.field_value.length; i++) {
        this.builder_list.push(new EgSingle);
    }
    //构建首个例句
    this.builder_list.push(new EgSingle);
    this.builder_list[0].select_builder.in_json = mr.find_json('user_source');
    this.builder_list[0].user_source_id = this.builder_list[0].select_builder.in_json[0]['id'];//默认user_source_id
    this.builder_list[0].build_edit();
    this.$dom.find('.padding-container').append(this.builder_list[0].$dom_edit);
    //构建其他例句
    for (let i = 0; i < this.builder_list.length; i++) {
        this.builder_list[i].data_index = i;
    }
    for (let i = 1; i < this.builder_list.length; i++) {
        this.builder_list[i].select_builder.in_json = mr.find_json('user_source');
        this.builder_list[i].eng_eg = this.field_value[i - 1]['eng_eg'];
        this.builder_list[i].eng_word = this.field_value[i - 1]['eng_word'];
        this.builder_list[i].chi_eg = this.field_value[i - 1]['chi_eg'];
        this.builder_list[i].chi_word = this.field_value[i - 1]['chi_word'];
        this.builder_list[i].key_id = this.field_value[i - 1]['value'];
        this.builder_list[i].user_source_id = this.field_value[i - 1]['user_source_id'];
        //todo 把select_builder.in_json换成内置属性
        this.builder_list[i].build_show();
        this.$dom.find('.padding-container').append(this.builder_list[i].$dom_show)
    }

};

var EgSingle = function () {
    this.eng_eg = '';
    this.eng_word = '';
    this.chi_eg = '';
    this.chi_word = '';

    this.generated_str = '';

    this.key_id = 0;
    this.user_word_id = 0;
    this.user_source_id = 0;
    this.user_source_value = '';
    this.data_index = 0;

    this.$dom_show = undefined;
    this.$dom_edit = undefined;

    this.select_builder = new ThSelectWidgetBuilder();
    this.select_builder.width = '100%';
};

EgSingle.prototype = {
    generate_bold: function () {
        /**
         * 生成加粗的例句
         * @type {RegExp}
         */
        let reg_eng = new RegExp(this.eng_word, 'g');
        let reg_chi = new RegExp(this.chi_word, 'g');
        this.generated_str =
            this.eng_eg.replace(reg_eng, '<strong>' + this.eng_word + '</strong>') +
            '<hr>' +
            this.chi_eg.replace(reg_chi, '<strong>' + this.chi_word + '</strong>');
    },
    get_val: function () {
        this.eng_eg = this.$dom_edit.find('.item__value__eng-eg').val();
        this.eng_word = this.$dom_edit.find('.item__value__eng-word').val();
        this.chi_eg = this.$dom_edit.find('.item__value__chi-eg').val();
        this.chi_word = this.$dom_edit.find('.item__value__chi-word').val();
    },
    build_show: function () {
        this.$dom_show = $(`<div class="item__value show">
    <div class="item__value__flow--horizon show">
        <div class="ui-widget-content ui-corner-all div-input show">
            <div class="sub-div-input"></div>
        </div>
    </div>
    <div class="item__value__flow--horizon pad">
        <div class="div-input source ui-widget-content ui-corner-all"></div>
        <button class="item__value__eg-edit-btn">编辑</button>
    </div>
</div>
`);
        //取得当前user_source_name
        for (let o = 0; o < this.select_builder.in_json.length; o++) {
            if (this.select_builder.in_json[o]['id'] === this.user_source_id) {
                this.user_source_value = this.select_builder.in_json[o]['value'];
            }
        }
        this.select_builder.first_option = this.user_source_value;
        //id
        this.$dom_show.attr('data-index', this.data_index);
        //改值
        this.generate_bold();
        this.$dom_show.find('.sub-div-input').html(this.generated_str);
        this.$dom_show.find('.div-input.source').html(this.user_source_value);
        //初始化按钮
        this.$dom_show.find('button').button();
        this.$dom_show.find('.item__value__eg-edit-btn').on('click', function () {

        })
    },
    build_edit: function () {
        this.$dom_edit = $(`<div class="item__value edit">
    <div class="item__value__flow--vertical">
        <textarea class="ui-widget-content ui-corner-all item__value__eng-eg"></textarea>
        <input class="ui-widget-content ui-corner-all item__value__eng-word">
        <select></select>
    </div>
    <div class="item__value__flow--vertical">
        <textarea class="ui-widget-content ui-corner-all item__value__chi-eg"></textarea>
        <input class="ui-widget-content ui-corner-all item__value__chi-word">
        <button class="item__value__eg-submit-btn">提交</button>
        <button class="item__value__eg-cancel-btn">取消</button>
    </div>
</div>
</div>
`);
        this.$dom_edit.attr('data-index', this.data_index);
        this.$dom_edit.find('.item__value__eng-eg').val(this.eng_eg);
        this.$dom_edit.find('.item__value__eng-word').val(this.eng_word);
        this.$dom_edit.find('.item__value__chi-eg').val(this.chi_eg);
        this.$dom_edit.find('.item__value__chi-word').val(this.chi_word);
        //初始化按钮
        this.$dom_edit.find('button').button();
        this.select_builder.$selector = this.$dom_edit.find('select');
        this.select_builder.width = '9em';
        this.select_builder.option_width = 200;
        this.select_builder.build();
    }
};

var ThControlUpDownResultItemBuilder = function () {
    ThBaseResultItemBuilder.call(this);
};
ThControlUpDownResultItemBuilder.prototype = new ThBaseResultItemBuilder();
ThControlUpDownResultItemBuilder.prototype.sub_build = function () {
    this.$dom.find('.item__value').append('<button id="main__result__up-btn">上一个</button>');
    this.$dom.find('.item__value').append('<button id="main__result__down-btn">下一个</button>');
    this.$dom.find('button').button();
    this.$dom.find('#main__result__up-btn').on('click', function () {
        ml.choose_back()
    });
    this.$dom.find('#main__result__down-btn').on('click', function () {
        ml.choose_next()
    });
};

