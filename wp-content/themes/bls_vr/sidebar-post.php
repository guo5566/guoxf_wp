<?php
if ( have_posts() ) :
add_action("wp_footer","bls_vr_footer_scripts_plus",100);
function bls_vr_footer_scripts_plus(){
?>
<!-- //投诉 -->
<div class="weui_actionsheet" id="report">
    <div class="weui_cells_title">请选择投诉原因</div>
    <div class="weui_cells weui_cells_radio">
        <label class="weui_cell weui_check_label" for="r1">
            <div class="weui_cell_bd weui_cell_primary">
                <p>欺诈</p>
            </div>
            <div class="weui_cell_ft">
                <input type="radio" class="weui_check" name="report" id="r1">
                <span class="weui_icon_checked"></span>
            </div>
        </label>
        <label class="weui_cell weui_check_label" for="r2">
            <div class="weui_cell_bd weui_cell_primary">
                <p>色情</p>
            </div>
            <div class="weui_cell_ft">
                <input type="radio" class="weui_check" name="report" id="r2">
                <span class="weui_icon_checked"></span>
            </div>
        </label>
        <label class="weui_cell weui_check_label" for="r3">
            <div class="weui_cell_bd weui_cell_primary">
                <p>政治谣言</p>
            </div>
            <div class="weui_cell_ft">
                <input type="radio" class="weui_check" name="report" id="r3">
                <span class="weui_icon_checked"></span>
            </div>
        </label>
        <label class="weui_cell weui_check_label" for="r4">
            <div class="weui_cell_bd weui_cell_primary">
                <p>常识性谣言</p>
            </div>
            <div class="weui_cell_ft">
                <input type="radio" class="weui_check" name="report" id="r4">
                <span class="weui_icon_checked"></span>
            </div>
        </label>
        <label class="weui_cell weui_check_label" for="r5">
            <div class="weui_cell_bd weui_cell_primary">
                <p>诱导分享</p>
            </div>
            <div class="weui_cell_ft">
                <input type="radio" class="weui_check" name="report" id="r5">
                <span class="weui_icon_checked"></span>
            </div>
        </label>
        <label class="weui_cell weui_check_label" for="r6">
            <div class="weui_cell_bd weui_cell_primary">
                <p>恶意营销</p>
            </div>
            <div class="weui_cell_ft">
                <input type="radio" class="weui_check" name="report" id="r6">
                <span class="weui_icon_checked"></span>
            </div>
        </label>
        <label class="weui_cell weui_check_label" for="r7">
            <div class="weui_cell_bd weui_cell_primary">
                <p>隐私信息收集</p>
            </div>
            <div class="weui_cell_ft">
                <input type="radio" class="weui_check" name="report" id="r7">
                <span class="weui_icon_checked"></span>
            </div>
        </label>
        <label class="weui_cell weui_check_label" for="r8">
            <div class="weui_cell_bd weui_cell_primary">
                <p>抄袭公众号文章</p>
            </div>
            <div class="weui_cell_ft">
                <input type="radio" class="weui_check" name="report" id="r8">
                <span class="weui_icon_checked"></span>
            </div>
        </label>
        <label class="weui_cell weui_check_label" for="r9">
            <div class="weui_cell_bd weui_cell_primary">
                <p>其他侵权类（冒名、诽谤、抄袭）</p>
            </div>
            <div class="weui_cell_ft">
                <input type="radio" class="weui_check" name="report" id="r9">
                <span class="weui_icon_checked"></span>
            </div>
        </label>
        <label class="weui_cell weui_check_label" for="r10">
            <div class="weui_cell_bd weui_cell_primary">
                <p>违规声明原创</p>
            </div>
            <div class="weui_cell_ft">
                <input type="radio" class="weui_check" name="report" id="r10">
                <span class="weui_icon_checked"></span>
            </div>
        </label>
    </div>
    <div class="weui_actionsheet_action">
        <div class="weui_actionsheet_cell" id="actionsheet_cancel" style="background-color:transparent; margin-left:10px;margin-right:10px;">
            <a class="weui_btn weui_btn_primary" href="javascript:showmsg();">下一步</a>
        </div>
    </div>
</div>
<div class="msg" id="msg">
    <div class="weui_msg">
        <div class="weui_icon_area"><i class="weui_icon_success weui_icon_msg"></i></div>
        <div class="weui_text_area">
            <h2 class="weui_msg_title">操作成功</h2>
            <p class="weui_msg_desc">感谢您的参与，我们坚决反对色情、暴力、欺诈等违规信息，我们会认真处理你的投诉，维护绿色、健康的网络环境。</p>
        </div>
        <div class="weui_opr_area">
            <p class="weui_btn_area">
                <a href="javascript:closeit();" role="button" class="weui_btn weui_btn_primary">确定</a>
            </p>
        </div>
    </div>
</div>
<script>
function showqr(){
    document.getElementById("js_profile_qrcode").style.display = "block";
}
function hideqr(){
    document.getElementById("js_profile_qrcode").style.display = "none";
}
function showmsg(){
    var b = document.getElementsByTagName("body")[0].className;
    document.getElementsByTagName("body")[0].className = b+" in";
}
function closeit(){
    if (typeof WeixinJSBridge == "undefined"){
        window.opener=null;
        window.open("","_self");
        window.close();
    } else {
        WeixinJSBridge.call("closeWindow");
    }
}
</script>
<?php
}
    while ( have_posts() ) : the_post();
?>
<div class="article">
    <h2 class="post-title"><?php the_title(); ?></h2>
    <div class="post-metas"> <span><?php the_date(); ?></span> <span><?php the_author(); ?></span> <a href="javascript:;"><?php bloginfo("name"); ?></a></div>
    <div class="weui_article"><?php the_content(); ?></div>
    <div class="post-metas"> <a href="<?php the_permalink(); ?>">阅读原文</a> <span>阅读 <?php flh_count( get_the_ID(), "view", true, true ); ?> </span> <a role="actionsheet" href="#report" style="position:absolute; right:15px; top:0px;">投诉</a> </div>
</div>
<?php get_sidebar(); ?>
<?php
    endwhile;
else:
    get_sidebar("none");
endif;
?>