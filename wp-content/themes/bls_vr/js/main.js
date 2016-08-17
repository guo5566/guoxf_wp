var global = this;

/**
 * 封装toast+dialog+actionsheet
 * $.toast(boolean|{type : "nomal|loading",text : "已完成",time : 3000,fn : ''})
 * role="dialog" data-dialog|href=DOM
 * role="actionsheet" data-actionsheet|href=DOM
 * role="load" data-load|href=URL data-page=页码
 * role="tab" 折叠
 * role="zan" 点赞
 * role="view" 折叠查看全部
**/
//封装toast+dialog+actionsheet
(function ($) {
    $.extend({
        "toast": function (options) {
            if($.type(options)==="boolean") options = {
                type : (options ? "nomal" : "loading")
            }
            if($.type(options)==="string") options = {
                text : options
            }
            var params = $.extend({}, {
                type : "nomal",
                text : "已完成",
                time : 1500,
                fn : ''
            }, options),
            html = '';
            if(params.type != "loading"){
                html += '<div id="toast">';
                html += '    <div class="weui_mask_transparent"></div>';
                html += '    <div class="weui_toast">';
                html += '        <i class="weui_icon_toast"></i>';
                html += '        <p class="weui_toast_content">'+params.text+'</p>';
                html += '    </div>';
                html += '</div>';
            } else {
                html += '<div id="toast" class="weui_loading_toast">';
                html += '    <div class="weui_mask_transparent"></div>';
                html += '    <div class="weui_toast">';
                html += '        <div class="weui_loading"></div>';
                html += '        <p class="weui_toast_content">'+params.text+'</p>';
                html += '    </div>';
                html += '</div>';
            }
            $("#toast").remove();
            $("body").append(html);
            if($.type(params.fn)==="function") params.fn(); else setTimeout(function(){
                $("#toast").remove();
            },params.time);
        },
        "toptips": function (html,fn,delay) {
            if($.type(html)!=="function" || $.type(html)!=="array"){
                $("body").append('<div class="weui_toptips weui_warn js_tooltips">'+html+'</div>');
                $(".weui_toptips:last").show();
                delay = $.type(delay)==="number" ? delay : 2000;
                setTimeout(function(){
                    $(".weui_toptips").remove();
                    if($.type(fn)==="function") fn();
                },delay);
            }
        }
    });
    $.fn.extend({
        "modal": function (display) {
            if(display=="show"){
                $(this).addClass("active");
            }
            if(display=="hide"){
                $(this).removeClass("active");
            }
        },
        "sheet": function (display) {
            function hideActionSheet(weuiActionsheet, weuiMask) {
                weuiActionsheet.removeClass('weui_actionsheet_toggle');
                weuiActionsheet.on('transitionend', function () {
                    weuiMask.remove();
                }).on('webkitTransitionEnd', function () {
                    weuiMask.remove();
                });
            }
            if(display=="show"){
                $("body").append('<div class="weui_mask_transition"></div>');
                var actionsheet = $(this),mask = $(".weui_mask_transition");
                actionsheet.addClass('weui_actionsheet_toggle');
                mask.show().addClass('weui_fade_toggle').unbind().bind("click",function () {
                    hideActionSheet(actionsheet, mask);
                });
                actionsheet.find('.weui_actionsheet_cell').unbind().bind("click",function () {
                    hideActionSheet(actionsheet, mask);
                });
                actionsheet.unbind('transitionend').unbind('webkitTransitionEnd');
            }
            if(display=="hide"){
                var mask = $(".weui_mask_transition");
                hideActionSheet($(this), mask);
            }
        }
    });
    $(document).on("click","[role=dialog]",function(e){
        var t = $(this),obj = (t.data("dialog") || t.attr("href")),dialog = $(obj);
        dialog.addClass("active");
    }).on("click","[role=load]",function(e){
        e.preventDefault();
        var t = $(this),loadurl = (t.data("load") || t.attr("href")),paged = $.type(t.data("page"))==="number" ? Number(t.data("page")) : 1,wrap = $(t.data("wrap")),
            html  = '    <div class="weui_toast" id="loading">';
            html += '        <div class="weui_loading"></div>';
            html += '    </div>';
        wrap.append(html);
        t.hide();
        paged++;
        $.post(loadurl, { page_num: paged }, function(data){
            $("#loading").remove();
            if(data!==""){
                wrap.append(data);
                t.data("page",paged).show();
            } else {
                wrap.append('<div class="weui_cells_tips">已经没有了！</div>');
            }
        });
    }).on("click","[role=loadpage]",function(e){
        e.preventDefault();
        var t = $(this),loadurl = t.attr("href"),wrap_dom = t.data("wrap"),wrap = $(t.data("wrap"));
        t.hide();
        $("body").addClass("in").append('<div id="result" style="display:none"></div>');
        $("#result").load(loadurl+" "+wrap_dom,function() {
            wrap.append($("#result").find(wrap_dom).html());
            $("body").removeClass("in");
            $("#result").load(loadurl+" [role=loadpage]",function() {
                newurl = $("#result").find("[role=loadpage]").attr("href");
                if($.type(newurl)!=="undefined" && newurl != loadurl){
                    t.attr("href", newurl).show();
                }
                $("#result").remove();
            });
        });
    }).on("click",".weui_btn_dialog",function(){
        $("body").removeClass("in");
    }).on("click",".weui_mask",function(){
        $(this).parent().removeClass("active");
    }).on("click","[role=actionsheet]",function(){
        var t = $(this),obj = (t.data("actionsheet") || t.attr("href"));
        $(obj).sheet("show");
    }).on("input propertychange",".weui_textarea",function(){
        var t = $(this),counter = t.siblings(".weui_textarea_counter").find("span");
        counter.text(t.val().length);        
    }).on("click","[role=tab] .weui_navbar_item",function(){
        var t = $(this),obj = t.parents("[role=tab]"),i = t.index();
        t.addClass('weui_bar_item_on').siblings('.weui_navbar_item').removeClass('weui_bar_item_on');
        obj.find(".weui_tab_bd").children("div").hide().eq(i).show();
    }).on("click","[role=search] form",function(){
        var t = $(this).parents("[role=search]"),obj = t.data("search");
        if(!t.hasClass('weui_search_focusing')){
            t.addClass('weui_search_focusing');
            t.find('input[type=search]').focus().select();
            if (t.find('input[type=search]').val()!="") {
                $(obj).show();
            } else {
                $(obj).hide();
            }
        }
    }).on("mouseout","[role=search]",function(){
        //var t = $(this),obj = t.data("search");
        //$(this).removeClass('weui_search_focusing');
        //$(obj).hide();
    }).on("input propertychange","[role=search] input[type=search]",function(){
        var t = $(this).parents("[role=search]"),wrap = $(t.data("search")),url = $(this).parents("form").attr("action");
        if ($.trim($(this).val())!="") {
            //ajax 获取结果
            html  = '    <div class="weui_toast" id="loading">';
            html += '        <div class="weui_loading"></div>';
            html += '    </div>';
            wrap.html(html).show();
            $.post(url, { key : $(this).val() }, function(data){
                $("#loading").remove();
                if(data!==""){
                    wrap.html(data);
                } else {
                    wrap.html('<div class="weui_cell"><div class="weui_cell_bd weui_cell_primary"><p>未能搜索到查找的内容！</p></div></div>');
                }
            });
        } else {
            wrap.hide();
        }
    }).on("click",".weui_search_cancel",function(){
        var t = $(this).parents("[role=search]"),obj = t.data("search");
        $(obj).hide();
        t.removeClass('weui_search_focusing').find('input[type=search]').val('');
    }).on("click",".weui_icon_clear",function(){
        var t = $(this).parents("[role=search]"),obj = t.data("search");
        $(obj).hide();
        t.find('input[type=search]').val('');
    }).on("click","[role=zan]",function(e){
        e.preventDefault();        
        var t = $(this),c = t.find(".bls"),zan = t.data("zan")>0 ? -1 : 1,url = t.attr("href") || t.data("href");
        $.post(url,{like: zan},function(i) {
            if(i != t.find("em").text()){
                t.data("zan", zan);
                if(zan>0){
                    if(c.hasClass("icon-zan-o")){
                        c.removeClass("icon-zan-o").addClass("icon-zan");
                    }
                    if(c.hasClass("icon-like-o")){
                        c.removeClass("icon-like-o").addClass("icon-like");
                    }     
                    t.find("p").html("已收藏");               
                } else {
                    if(c.hasClass("icon-zan")){
                        c.removeClass("icon-zan").addClass("icon-zan-o");
                    }
                    if(c.hasClass("icon-like")){
                        c.removeClass("icon-like").addClass("icon-like-o");
                    }    
                    t.find("p").html("收藏");   
                }
                t.find("em").html(i);
            } else {
                $.toptips("需要登录才能点赞！");
            }
        });
    }).on("click","[role=view]",function(e){
        e.preventDefault();
        var t = $(this),view = t.parents(".weui_media_text");
        if(view.hasClass("weui_media_text_all")){
            view.removeClass("weui_media_text_all");
            t.html("显示全部 &raquo;");
        } else {
            view.addClass("weui_media_text_all");
            t.html("折叠显示 &laquo;");
        }
    }).on("click","#loading",function(e){
        $("#loading").fadeOut();
    });
    if($('#loading').length){
        $("#loading").fadeOut();
    }
})(window.jQuery);