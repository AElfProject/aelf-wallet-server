;
var jsonModal = (function(){
    var _modal = function(textareaE1, modelE1) {
        var $textarea = $(textareaE1);
        var $modal = $(modelE1);

        _initModal(modelE1);//初始化弹框

        $textarea.attr("readonly",true);

        $textarea.on('click',function () {
            
            $(modelE1 + " #index").val($textarea.index(this));//赋值当前的textarea索引
            $modal.find('.modal-textarea').val('');
            try {
                var jsonObj =  _parse_json_by_JSON_parse($(this).val());
                for(var p in jsonObj){//遍历json对象的每个key/value对,p为key
                    if (jsonObj[p]) {
                        $(modelE1 + ' #'+ p).val(jsonObj[p]);
                    }else{
                        $(modelE1 + ' #'+ p).val('');
                    }
                }
            }catch (e) {
                console.log(e)
            }

            $modal.css("display","block");
        })

        $modal.find('#update').on('click',function () {
            var json = {};
            $modal.find('.modal-textarea').each(function(){
                var jsonKey = $(this).attr("id");
                var jsonValue = $(this).val();
                json[jsonKey] = jsonValue;
            });

            $('.json-textarea').eq($("input[name='index']").val()).val(JSON.stringify(json));
            $modal.hide();
        });

        $modal.find('#back').on('click',function () {
            $modal.hide();
        });

    };
    
    var _initModal = function (modelE1) {
        var Mt_hei = $(modelE1 + ">:first").height();
        var Windows_hei = $(window).height();
        $(modelE1 + ">:first").css("margin-top", (Windows_hei - Mt_hei) / 2);

        $(window).resize(function () {
            Windows_hei = $(window).height();
            $(modelE1 + ">:first").css("margin-top", (Windows_hei - Mt_hei) / 2);
        })

        $(modelE1).click(function () {
            $(this).hide();
        })

        $(modelE1 + ">:first").click(function () {
            return false;//清除冒泡
        })

    }

    var _parse_json_by_JSON_parse = function(str) {
        return JSON.parse(str);
    }

    return {
        modal:_modal,
    }
}())