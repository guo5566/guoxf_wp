<?php
/**
 * 设定 * 核心
 * ver 1.0 core
**/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 配置
**/
function bls_vr_settings_fn(){
    global $wpdb;
    wp_enqueue_media();
    $timestamp = current_time("timestamp");
    $ajax_none = wp_create_nonce( "bls_vr_nonce" );
    $str = "";
    $bls_vr_settings_attr = array (
        array(
            "title" => __( '原始ID', 'bls-vr' ),
            "desc"  => __( '登录微信公众平台=>公众号设置 查看', 'bls-vr' ),
            "name"  => "wxid",
            "type"  => "text",
            "std"   => ''
        ),
        array(
            "title" => __( 'AppID(应用ID)', 'bls-vr' ),
            "desc"  => __( '登录微信公众平台=>开发者中心 查看', 'bls-vr' ),
            "name"  => "appid",
            "type"  => "text",
            "std"   => ''
        ),
        array(
            "title" => __( 'AppSecret(应用密钥)', 'bls-vr' ),
            "desc"  => __( '登录微信公众平台=>开发者中心 查看', 'bls-vr' ),
            "name"  => "appsecret",
            "type"  => "text",
            "std"   => ''
        ),
        array(
            "title" => __( '开发者中心请填写URL为该段内容', 'bls-vr' ),
            "desc"  => __( '必须以http://开头，目前支持80端口。', 'bls-vr' ),
            "name"  => "appurl",
            "type"  => "read",
            "std"   => home_url("/")
        ),
        array(
            "title" => __( '开发者中心请填写Token为该段内容', 'bls-vr' ),
            "desc"  => __( '必须为英文或数字，长度为3-32字符。', 'bls-vr' ),
            "name"  => "apptoken",
            "type"  => "text",
            "std"   => bls_rand_str(rand(3,32))
        ),
        array(
            "title" => __( '开发者中心请填写EncodingAESKey为该段内容', 'bls-vr' ),
            "desc"  => __( '消息加密密钥由43位字符组成，可随机修改，字符范围为A-Z，a-z，0-9。', 'bls-vr' ),
            "name"  => "encodingaeskey",
            "type"  => "text",
            "std"   => bls_rand_str(43)
        ),
        array(
            "title" => __( '开发者中心请选择同样的消息加解密方式', 'bls-vr' ),
            "desc"  => __( '请根据业务需要，选择消息加解密类型', 'bls-vr' ),
            "name"  => "msgtype",
            "type"  => "radio",
            "std"   => 0,
            "radio" => array(
                 0=>array("title"=>__( "明文模式", 'bls-vr' ),"desc"=>__( "明文模式下，不使用消息体加解密功能，安全系数较低", 'bls-vr' )),
                 1=>array("title"=>__( "兼容模式", 'bls-vr' ),"desc"=>__( "兼容模式下，明文、密文将共存，方便开发者调试和维护", 'bls-vr' )),
                 2=>array("title"=>__( "安全模式（推荐）", 'bls-vr' ),"desc"=>__( "安全模式下，消息包为纯密文，需要开发者加密和解密，安全系数高", 'bls-vr' ))
            )
        ),
        array(
            "title" => __( 'Mchid(微信支付商户号)', 'bls-vr' ),
            "desc"  => __( '登录微信公众平台=>微信支付=>商户信息=>查看商户号', 'bls-vr' ),
            "name"  => "wxpay_mchid",
            "type"  => "text",
            "std"   => ''
        ),
        array(
            "title" => __( 'KEY(商户平台登录密码)', 'bls-vr' ),
            "desc"  => __( '新用户首次需要设置：登录商户平台=>账户设置=>API安全=>API密钥', 'bls-vr' ).'<br><a target="_blank" href="https://pay.weixin.qq.com/index.php/account/api_cert"><code>https://pay.weixin.qq.com/index.php/account/api_cert</code></a>',
            "name"  => "wxpay_key",
            "type"  => "text",
            "std"   => ''
        ),
        array(
            "title" => __( '首页Banner', 'bls-vr' ),
            "desc"  => __( '会自动调用最后一篇有该图片的文章。如果不设置则不会显示轮换图片', 'bls-vr' ),
            "name"  => "banner",
            "type"  => "thumb",
            "std"   => ''
        ),
        array(
            "title" => __( '体验馆', 'bls-vr' ),
            "desc"  => __( '用户查看预约码时显示的体验馆名称', 'bls-vr' ),
            "name"  => "shop",
            "type"  => "text",
            "std"   => ''
        ),
        array(
            "title" => __( '地址', 'bls-vr' ),
            "desc"  => __( '用户查看预约码时显示的体验馆地址', 'bls-vr' ),
            "name"  => "address",
            "type"  => "text",
            "std"   => ''
        ),
        array(
            "title" => __( '手机', 'bls-vr' ),
            "desc"  => __( '用户查看预约码时显示的手机号码', 'bls-vr' ),
            "name"  => "phone",
            "type"  => "text",
            "std"   => ''
        ),
        array(
            "title" => __( '注意事项', 'bls-vr' ),
            "desc"  => '<a target="_blank" href="http://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=7_3"><code>'.__( '请登录微信公众平台=>微信支付=>开发配置=>', 'bls-vr' ).'</code></a>'.
                '<br>'.
                __( '填写支付授权目录为：', 'bls-vr' ).preg_replace("/(\?.+)$/","",checkout_url).
                '<br>'.
                __( '填写支付回调URL为：', 'bls-vr' ).home_url("/?action=wxpay_notify").
                '<br>'.
                __( '游戏中心URL为：', 'bls-vr' ).genre_url.
                '<br>'.
                __( '套餐选购URL为：', 'bls-vr' ).mall_url.
                '<br>'.
                __( '用户中心URL为：', 'bls-vr' ).user_url,
            "name"  => "wxpay_notify",
            "type"  => "desc",
            "std"   => ''
        )
    );
    $bls_vr_settings_attr = apply_filters("bls_vr_settings_attr", $bls_vr_settings_attr);
    //我们把所有变量保存到 wp-config.php 的 bls_vr_data 常量中来减少数据库读写次数
    $bls_vr_data = defined("bls_vr_data") ? maybe_unserialize(bls_vr_data) : array();
    if(!is_array($bls_vr_data)) $bls_vr_data = array();
    //初始化&升级时执行
    if(!isset($bls_vr_data["version"]) || (isset($bls_vr_data["version"]) && version_compare($bls_vr_data["version"], bls_vr_ver) >= 0)){
        //创建微信平台所需要的身份
        add_role('bls_customer' , __( '客户' ,'bls-vr'), array('read' => 1, ));
        add_role('bls_staff'    , __( '员工' ,'bls-vr'), array('read' => 1, ));
        //创建保存用户消息回馈规则的数据库
        $table_msg       = $wpdb->prefix . 'msg';        //通信存储
        $table_msg_meta  = $wpdb->prefix . 'msg_meta';   //通信存储meta
        $table_object    = $wpdb->prefix . 'object';     //项目数据库
        $charset_collate = $wpdb->get_charset_collate();
        
        //msg_id        消息唯一编号
        //msg_to_user   消息接收用户
        //msg_from_user 消息发送用户
        //msg_type      消息类型
        //msg_time      消息创建时间
        $sql_msg = "CREATE TABLE $table_msg (
            msg_id mediumint(9) NOT NULL AUTO_INCREMENT,
            msg_to_user varchar(255),
            msg_from_user varchar(255),
            msg_type varchar(20),
            msg_time varchar(20),
            UNIQUE KEY msg_id (msg_id)
        ) $charset_collate;";
            
        //id        数据库自编号
        //msg_id    消息唯一编号
        //msg_key   消息关键值
        //msg_value 消息内容值
        $sql_msg_meta = "CREATE TABLE $table_msg_meta (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            msg_id bigint(20),
            msg_key tinytext,
            msg_value text,
            UNIQUE KEY id (id)
        ) $charset_collate;";
            
        //object_id         项目自编号
        //user_id           归属的用户ID
        //object_order_no   订单编号
        //object_order_time 订单时间戳
        //object_goods      购买套餐 post_id
        //object_price      支付总金额
        //object_cost       实际支付金额
        //object_ticket     使用的票券 post_title
        //object_note       票券使用描述:抵扣10元
        //object_code       预约码 post_title
        //object_status     状态 0待用 1已经使用
        $sql_object = "CREATE TABLE $table_object (
            object_id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20),
            object_order_no varchar(255),
            object_order_time varchar(255),
            object_goods varchar(255),
            object_price varchar(255),
            object_cost varchar(255),
            object_ticket varchar(255),
            object_note text,
            object_code varchar(255),
            object_status varchar(255),
            UNIQUE KEY object_id (object_id)
        ) $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql_msg );
        dbDelta( $sql_msg_meta );
        dbDelta( $sql_object );
        //更新版本值
        $bls_vr_data["version"] = bls_vr_ver;
        //写入初始默认值
		    if(count($bls_vr_settings_attr)>0){
            foreach($bls_vr_settings_attr as $key=>$val){
                if(isset($val["name"])){
                    $name = $val["name"];
                    $std = isset($val["std"]) ? $val["std"] : "";
                    $value = get_bls_vr_option($name,$std);
                    $bls_vr_data[$name] = $value;
                }
            }
        }
        update_constant("bls_vr_data",$bls_vr_data);
    }
    //保存操作
    if(isset($_POST['action'])){
        if(count($bls_vr_settings_attr)>0){
            foreach($bls_vr_settings_attr as $key=>$val){
                if(isset($val["name"])){
                    $name = $val["name"];
                    $value = isset($_POST[$name]) ? str_replace($order, $replace, $_POST[$name]) : "";
                    $bls_vr_data[$name] = $value;
                }
            }
        }
        $bls_vr_data["access_token_time"] = $timestamp;//强制更新
        update_constant("bls_vr_data",$bls_vr_data);
        wp_redirect(admin_url('themes.php?page=bls_vr_settings&settings-updated=true'));
        exit;
    }    
?>
<style>
  .thumb-items{
    display: block;
  }  
  .thumb-items + p,
  .thumb-items:after{
    clear: both;
    width: 100%;
    min-height: 5px;
    content: ' ';
  }
  .thumb-items>*{
    float: left;
    box-sizing:border-box;
    display:block;
    position:relative;
    max-width:33.333333333%;
    min-width:100px;
    height:100px;
    padding:5px;
    margin-bottom: 10px;
    text-align:center;
    overflow: hidden;
  }
  .thumb-items>span{
    border:1px solid #DEDEDE;
    margin-right: 15px;
  }
  .thumb-items>span>img{
    max-width:100%;
    max-height:88px;
    display:block;
    margin:0 auto;
    position:relative;
  }
  .thumb-items>span>.close{
    font-style:normal;
    position:absolute;
    right:3px;
    bottom:3px;
    width:30px;
    text-align:center;
    height:30px;
    line-height:30px;
    cursor:pointer;
    font-size:24px;
    z-index:2;
    background-color:#eee;
    opacity:.75;
  }
  .thumb-items>.choose-album{
    box-sizing:border-box;
    display:block;
    position:relative;
    width:71px;
    height:100px;
    border:1px dashed #DEDEDE;
    background-color:#eee;
    position: relative;
  }
  .thumb-items>.choose-album:before,
  .thumb-items>.choose-album:after{
    content: ' ';
    background-color: #ccc;
    position:absolute;
    left: 50%;
    top: 50%;
    border-radius: 3px;
  }
  .thumb-items>.choose-album:before{
    width: 8px;
    height: 50px;
    margin: -25px auto auto -4px;
  }
  .thumb-items>.choose-album:after{
    width: 50px;
    height: 8px;
    margin: -4px auto auto -25px;
  }
</style>
<div class="wrap">
    <h1>主题配置选项</h1>
<?php
    if(isset($_GET["settings-updated"])){
?>
<div class="updated">
    <p>设置保存成功！</p>
</div>
<?php
    }
?>
    <form novalidate method="post" enctype="multipart/form-data" action="<?php echo admin_url("admin.php?page=bls_vr_settings"); ?>">
        <input type="hidden" name="action" value="update">
        <input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo $ajax_none; ?>">
        <table class="form-table">
            <tbody>
            <?php
            foreach($bls_vr_settings_attr as $key=>$val){
                $name = $val["name"];
                $value = get_bls_vr_option($name);
                switch($val["type"]){
                    case "text":
            ?>
                <tr>
                    <th scope="row"><label for="<?php echo $name; ?>"><?php echo $val["title"]; ?></label></th>
                    <td><input name="<?php echo $name; ?>" type="text" id="<?php echo $name; ?>" value="<?php esc_attr_e($value); ?>" class="regular-text">
                    <p class="description" id="<?php echo $name; ?>-description"><?php echo $val["desc"]; ?></p></td>
                </tr>
            <?php
                    break;
                    case "desc":
            ?>
                <tr>
                    <th scope="row"><label for="<?php echo $name; ?>"><?php echo $val["title"]; ?></label></th>
                    <td><p class="description"><?php echo $val["desc"]; ?></p></td>
                </tr>
            <?php
                    break;
                    case "textarea":
            ?>
                <tr>
                    <th scope="row"><label for="<?php echo $name; ?>"><?php echo $val["title"]; ?></label></th>
                    <td><textarea name="<?php echo $name; ?>" id="<?php echo $name; ?>" class="regular-text"><?php esc_attr_e($value); ?></textarea>
                    <p class="description" id="<?php echo $name; ?>-description"><?php echo $val["desc"]; ?></p></td></td>
                </tr>
            <?php
                    break;
                    case "radio":
            ?>
                <tr>
                    <th scope="row"><label for="<?php echo $name; ?>"><?php echo $val["title"]; ?></label></th>
                    <td>
                    <?php 
                    foreach($val["radio"] as $radio_value=>$radio){
                    ?>
                      <p>
                         <label>
                          <input type="radio" name="<?php echo $name; ?>" value="<?php echo $radio_value; ?>"<?php if($value==$radio_value) echo ' checked="checked"'; ?>>
                          <?php echo $radio["title"]; ?>
                        </label>
                        <br>
                        <?php echo $radio["desc"]; ?>
                      </p>
                    <?php
                    }
                    ?>
                    <p class="description" id="<?php echo $name; ?>-description"><?php echo $val["desc"]; ?></p></td></td>
                </tr>
            <?php
                    break;
                    case "thumb":
            ?>
                <tr>
                    <th scope="row"><label for="<?php echo $name; ?>"><?php echo $val["title"]; ?></label></th>
                    <td>
                    <p class="hide-if-no-js thumb-items">
            <?php
                    if($value){
                        $thumb_ids = preg_split("/\D/",$value);
                        foreach($thumb_ids as $num=>$aid){
                            $img_url = wp_get_attachment_image_src($aid,"full");
                            echo '<span><img src="'.$img_url[0].'" /><i data-id="'.$aid.'" class="close">&times;</i></span>';  
                        }
                    }
            ?>
                        <a href="javascript:;" class="choose-album" title="增加一张图片做Banner"></a>
                        <input name="<?php echo $name; ?>" value="<?php echo $value; ?>" type="hidden" />
                    </p>
                    <p class="description" id="<?php echo $name; ?>-description"><?php echo $val["desc"]; ?></p>
                    </td>
                </tr>
            <?php
                    break;
                }
            }
            ?> 
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="保存更改">
        </p>
    </form>
</div>
<script>
jQuery(document).ready(function($) {
    var bls_upload_frame = wp.media({
        title: '请选择一张图片',   
        button: {
            text: '确认选择',   
        },   
        multiple: false   
    });
    function refresh_fn(){
        $(".thumb-items").each(function() {
            var t=$(this), c=t.find(".close"), album = new Array(c.length);
            c.each(function(i) {
                album[i] = $(this).data("id");
            });
            t.children("input").val( album.join() );
        });
    }
    $(document).on("click",".close",function(e){
        $(".close").unbind().bind("click",function(){
            var t = $(this).parent();
            t.fadeOut(200);
            setTimeout(function(){
                t.remove();
                refresh_fn();
            },300);
        });
    }).on("click",".choose-album",function(e){
        var t=$(this);
        bls_upload_frame.open().off('select').on('select',function(){
            attachment_data = bls_upload_frame.state().get('selection').first().toJSON();
            t.before('<span><img src="'+ attachment_data.url +'" /><i data-id="'+ attachment_data.id +'" class="close">&times;</i></span>');
            refresh_fn();
        }); 
    });
});
</script>
<?php
}

/**
 * Change code lost you hands
**/