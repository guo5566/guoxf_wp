<?php
/**
 * 初始化 * 核心
 * ver 1.0 core
**/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 初始化时执行
 */
add_action('init', 'bls_vr_init',1);
function bls_vr_init() {
    $timestamp = current_time("timestamp");
    //强制开启Session
    if(!session_id()) @session_start();
    //我们把本插件的所有变量保存到 wp-config.php 的 bls_vr_data 常量中;
    //好处：减少数据库读写次数
    $bls_vr_data = defined("bls_vr_data") ? maybe_unserialize(bls_vr_data) : array();
    if(!is_array($bls_vr_data)) $bls_vr_data = array();
    //进行常量定义
    if(isset($bls_vr_data["version"]) && version_compare($bls_vr_data["version"], bls_vr_data) >= 0){
        //定义常量之前先检查有效期
        $appid = isset($bls_vr_data["appid"]) ? $bls_vr_data["appid"] : "";//应用ID
        $appsecret = isset($bls_vr_data["appsecret"]) ? $bls_vr_data["appsecret"] : "";//应用密钥
        $access_token_time = isset($bls_vr_data["access_token_time"]) ? $bls_vr_data["access_token_time"] : "";//有效期
        //同步更新以下三个值
        $access_token = isset($bls_vr_data["access_token"]) ? $bls_vr_data["access_token"] : "";
        $jsapi_ticket = isset($bls_vr_data["jsapi_ticket"]) ? $bls_vr_data["jsapi_ticket"] : "";
        $cardapi_ticket = isset($bls_vr_data["cardapi_ticket"]) ? $bls_vr_data["cardapi_ticket"] : "";
        //自动在时间到期后刷新
        if($timestamp > $access_token_time && !empty($appid) && !empty($appsecret)){
            //更新access_token
            $access_token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=". $appid ."&secret=". $appsecret;
            //var_dump($access_token_url);
            $response = wp_remote_get( $access_token_url );
            $remote_token_data = wp_remote_retrieve_body( $response );
            //file_put_contents( '/tmp/wp.log', $response . "\n", FILE_APPEND );
            //var_dump($response);
            if ( $remote_token_data ){
                //{"access_token":"......","expires_in":7200}
                //{"errcode":40013,"errmsg":"invalid appid"}
                $token = json_decode($remote_token_data);
                if(isset($token->access_token)){
                    $access_token = $token->access_token;
                    $bls_vr_data["access_token"] = $access_token;
                    $bls_vr_data["access_token_time"] = $timestamp + $token->expires_in - 120;//提前两分钟更新
                    //-------------------------------------------------------//
                    //通过拿到的access_token 采用http GET方式请求获得jsapi_ticket
                    //-------------------------------------------------------//
                    $access_jsapi_ticket_url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=jsapi";
                    $remote_jsapi_ticket_data = wp_remote_retrieve_body( wp_remote_get( $access_jsapi_ticket_url ) );
                    if ( $remote_jsapi_ticket_data ){
                        //{"errcode":0,"errmsg":"ok","ticket":"......","expires_in":7200}
                        $jsapi = json_decode($remote_jsapi_ticket_data);
                        $bls_vr_data["jsapi_ticket"] = isset($jsapi->ticket) ? $jsapi->ticket : "";
                    } else {
                        //如果获取失败则刷新页面
                        add_action('admin_notices', 'bls_weixin_remote_error_notices');
                    }
                    //保存更新的值
                    update_constant("bls_vr_data",$bls_vr_data);
                }
            } else {
                //获取失败则发送通知
                add_action('admin_notices', 'bls_weixin_remote_error_notices');
            }
        }
        //定义常量：统一采用：缩写或下划线则全部大写，多单词则使用驼峰首字母大写
        //也可以使用get_bls_vr_option()函数调用
        $constant_arr = array(
            //"SAVEQUERIES"   => true,//分析wpdb查询次数
            "WXID"            => "wxid",//原始id
            "AppID"           => "appid",//应用ID
            "AppSecret"       => "appsecret",//应用密钥
            "AppToken"        => "apptoken",//消息加解密密钥
            "EncodingAESKey"  => "encodingaeskey",//令牌
            "MsgType"         => "msgtype",//消息加解密方式
            "ACCESS_TOKEN"    => "access_token",//通信票据
            "JSAPI_TICKET"    => "jsapi_ticket",//JS票据
            "Mchid"           => "wxpay_mchid",//商户登陆密码帐号
            "Mchkey"          => "wxpay_key",//商户登陆密码
            "apiclient_cert"  => bls_vr_url."wxpay/WxPayPubHelper/cacert/",
            "apiclient_key"   => bls_vr_url."wxpay/WxPayPubHelper/cacert/",
            "wxpay_notify_url"=> home_url("/?wxpay=notify")
        );
        $constant_arr = apply_filters("bls_vr_constant_attr", $constant_arr);
        foreach($constant_arr as $key=>$val){
            if(isset($bls_vr_data[$val]))
            $val = isset($bls_vr_data[$val]) ? $bls_vr_data[$val] : $val;
            if(!defined($key)) define($key, $val);
        }
    }
}

/**
 * 主题初始化
**/
function bls_vr_setup() {
    load_theme_textdomain( 'bls-vr', bls_vr_url('languages') );
    add_theme_support( 'post-thumbnails' );
    //我们要求微信模式下强行全自动注册+登录
    bls_vr_auto_login();
    //强制不显示前台工具栏
    add_filter('show_admin_bar', '__return_false');
}
add_action( 'after_setup_theme', 'bls_vr_setup' );

/**
 * 给管理者加上配置功能
**/
add_action('admin_menu', 'bls_vr_menu');
function bls_vr_menu() {
	add_menu_page( '券票', '券票', 'manage_options', 'bls_vr_tickets', 'bls_vr_tickets_fn', 'dashicons-tickets');
	//add_menu_page( '二维码券票', '二维码券票', 'manage_options', 'bls_vr_tickets1', 'bls_vr_tickets_fn1', 'dashicons-tickets');
	add_menu_page( '配置', '配置', 'manage_options', 'bls_vr_settings', 'bls_vr_settings_fn', 'dashicons-admin-generic');
	
	add_menu_page('设置营销验证码', '设置营销验证码', 'manage_options','qrcheckcode', 'display_checkcode_html_page');
	
	
}

/**
 * 主题输出样式脚本
**/
add_action('wp_enqueue_scripts', 'bls_vr_style_scripts' );
function bls_vr_style_scripts(){
    $debug  = WP_DEBUG ? "" : ".min";
    //先注册全部的样式
    wp_register_style( 'weui', bls_vr_url.'css/weui'.$debug.'.css', false, '1.0' );
    wp_register_style( 'bls', bls_vr_url.'fonts/iconfont.css', array('weui'), '1.0' );
    wp_register_style( 'swiper', bls_vr_url.'css/swiper'.$debug.'.css', false, '3.1.2' );
    wp_register_style( 'bls_vr', bls_vr_url.'style.css', array('swiper','bls'), '1.0' );
    //先注册全部的脚本
    wp_register_script( 'swiper', bls_vr_url.'js/swiper.jquery'.$debug.'.js', array('jquery'), '3.1.2', true );
    wp_register_script( 'bls_vr', bls_vr_url.'js/main.js', array('swiper'), '3.3.2', true );
    

    
    
    wp_register_script( 'jweixin', 'http://res.wx.qq.com/open/js/jweixin-1.0.0.js', false, '1.0.0', true );
    //默认加载的样式脚本
    wp_enqueue_style( 'bls_vr' );
    wp_enqueue_script( 'bls_vr' );
    //微信客户端专用
    if(is_weixin()){
        wp_enqueue_script( 'jweixin' );
    }
}
add_action("wp_footer","bls_vr_footer_scripts",100);
function bls_vr_footer_scripts(){
?>
<script>
jQuery(document).ready(function($) {
    $("#loading").click(function(e) {
        $("body").removeClass("in");
    }).fadeOut(3000, function(){
        //IE8以下版本不能正常浏览本站点
        if(navigator.userAgent.indexOf("MSIE 6.0")>0 || navigator.userAgent.indexOf("MSIE 7.0")>0) {
            $.toptips("请升级浏览器！或者使用非IE浏览器访问！");
        }
    });
    $("a:not([role=free])").bind("click", function(){
        $("body").addClass("in");
    });
<?php
/**
 * 微信客户端专用注意：
 * 1. 所有的JS接口只能在公众号绑定的域名下调用，公众号开发者需要先登录微信公众平台进入“公众号设置”的“功能设置”里填写“JS接口安全域名”。
 * 2. 如果发现在 Android 不能分享自定义内容，请到官网下载最新的包覆盖安装，Android 自定义分享接口需升级至 6.0.2.58 版本及以上。
 * 3. 常见问题及完整 JS-SDK 文档地址：http://mp.weixin.qq.com/wiki/7/aaa137b55fb2e0456bf8dd9148dd613f.html
 *
 * 开发中遇到问题详见文档“附录5-常见错误及解决办法”解决，如仍未能解决可通过以下渠道反馈：
 * 邮箱地址：weixin-open@qq.com
 * 邮件主题：【微信JS-SDK反馈】具体问题
 * 邮件内容说明：用简明的语言描述问题所在，并交代清楚遇到该问题的场景，可附上截屏图片，微信团队会尽快处理你的反馈。
**/
if(is_weixin()){
    // 注意 URL 一定要动态获取，不能 hardcode.
    $protocol   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $timestamp  = current_time("timestamp");
    $nonceStr   = bls_rand_str(16);
    $sring      = "jsapi_ticket=".JSAPI_TICKET."&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
    $signature  = sha1($sring);
?>
    wx.config({
        debug: false,
        appId: '<?php echo AppID; ?>',
        timestamp: <?php echo $timestamp; ?>,
        nonceStr: '<?php echo $nonceStr; ?>',
        signature: '<?php echo $signature; ?>',
        jsApiList: [
            // 所有要调用的 API 都要加到这个列表中
            'checkJsApi',
            'onMenuShareTimeline',
            'onMenuShareAppMessage'
        ]
    });
    wx.ready(function () {
        var shareData = {
            title: "<?php wp_title( ' ', true, 'right' ); bloginfo("name"); ?>",
            desc: "<?php
                $description = "";
                if( is_home() || is_front_page() ) {
                    $description = get_bloginfo( 'description', 'display' );
                } elseif( is_tax() || is_category() ) {
                    $description = (!empty($description) && get_query_var('paged')) ? category_description().__('(第','bls').get_query_var('paged').__('页)','bls') : category_description();
                } elseif (is_tag()) {
                    $description = (!empty($description) && get_query_var('paged')) ? tag_description().__('(第','bls').get_query_var('paged').__('页)','bls') : tag_description();
                } elseif (is_404()) {
                    $description = __('404错误!未能查找到页面内容','bls');
                } elseif (is_singular()) {
                    $description = wp_trim_words(get_the_excerpt(),220);
                }
                echo wp_trim_words(wp_kses($description, ""), 30);
            ?>",
            link: "<?php the_permalink(); ?>",
            imgUrl: "<?php bls_thumbnail(); ?>",
            success: function (res) {
                alert('已分享');
            },
            cancel: function (res) {
                alert('已取消');
            },
            fail: function (res) {
                alert(JSON.stringify(res));
            }
        };
        wx.onMenuShareAppMessage(shareData);
        wx.onMenuShareTimeline(shareData);
        wx.onMenuShareQQ(shareData);
        wx.onMenuShareWeibo(shareData);
    });
<?php
}
?>
});
</script>
<?php
}

/**
 * 创建或者查询需要的页面，并定义$slug.'_url'常量
**/
add_action("admin_init","bls_vr_admin_init");
function bls_vr_admin_init(){
    //好处：减少数据库读写次数
    $bls_vr_data = defined("bls_vr_data") ? maybe_unserialize(bls_vr_data) : array();
    if(!is_array($bls_vr_data)) $bls_vr_data = array();
    //无此字段时跳转到初始配置界面
    if(is_super_admin() && (!isset($bls_vr_data["version"]) || version_compare($bls_vr_data["version"], bls_vr_ver) < 0)){
        //让用户去填写自己的信息
        if(!isset($_GET["page"]) || isset($_GET["page"]) != "bls_vr_settings"){
            wp_safe_redirect(admin_url("admin.php?page=bls_vr_settings"));
            exit;
        }
    }
    //本插件至少需要有以下页面
    $custom_page = apply_filters("bls_weixin_repair_custom_pages",array(
        "user"      => __("用户中心",'bls-vr'),
        "checkout"  => __("购物车",'bls-vr'),
        "genre"     => __("游戏中心",'bls-vr'),
        "mall"      => __("套餐选购",'bls-vr')
    ));
    foreach($custom_page as $slug=>$title){
        $custom_post_ID = bls_weixin_repair_custom_page($slug,$title);
        $link = get_page_link($custom_post_ID);
        update_constant($slug.'_url', $link);
        if(!defined($slug.'_url')) define($slug.'_url', $link);
    }
}

/*
 * 统一用户体验，采用微软雅黑字体
**/
add_action('admin_print_footer_scripts', 'bls_vr_admin_print_footer_scripts' );
function bls_vr_admin_print_footer_scripts(){
?>
<style>
body {
    font-family: "Microsoft YaHei","微软雅黑",Helvetica,"黑体",Arial,Tahoma;
    font-size: 14px;
}
</style>
<?php
}

/*
 * 分析页面查询时间和次数
**
define('SAVEQUERIES', true);
add_action('bls_weixin_foot', 'bls_weixin_foot_fn');
function bls_weixin_foot_fn() {
    if (is_user_logged_in() && current_user_can('level_10')){
        echo "<pre>";
?>
<p> <?php echo get_num_queries(); ?> queries in <?php timer_stop(3); ?> seconds <p>
<?php
        global $wpdb;
        print_r($wpdb->queries);
        echo "</pre>";
    }
}

/*
 * Change code lost you hands
**/
