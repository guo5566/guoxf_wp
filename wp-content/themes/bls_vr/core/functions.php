<?php
/*
                         _ooOoo_
                        o8888888o
                        88" . "88
                        (| -_- |)
                        O\  =  /O
                     ____/`---'\____
                   .'  \\|     |//  `.
                  /  \\|||  :  |||//  \
                 /  _||||| -:- |||||-  \
                 |   | \\\  -  /// |   |
                 | \_|  ''\---/''  |   |
                 \  .-\__  `-`  ___/-. /
               ___`. .'  /--.--\  `. . __
            ."" '<  `.___\_<|>_/___.'  >'"".
           | | :  `- \`.;`\ _ /`;.`/ - ` : | |
           \  \ `-.   \_ __\ /__ _/   .-` /  /
      ======`-.____`-.___\_____/___.-`____.-'======
                         `=---='
      ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
            佛祖保佑      永无BUG       永不修改

//--------------------------------------------------------
// 请尊重劳动的成果, 加班几百小时, Change code lost you hands
//--------------------------------------------------------

/////// 开始工作, have fun! /////*/
define("bls_vr_ver", "1.0");
define("bls_vr", get_template_directory_uri()."/");
define("bls_vr_dir", get_template_directory()."/");

/**
 * 主题链接
**/
function bls_vr_url($str=""){
    return bls_vr.$str;
}
function bls_vr($str=""){
    echo bls_vr_url($str);
}
function get_bls_vr_dir($str=""){
    return bls_vr_dir.$str;
}

/**
 * 主题支持bls_weixin插件
**/
add_filter( 'bls_weixin_support', 'get_bls_vr_dir' );

/**
 * 主题支持bls_weixin插件
**/
add_filter( 'bls_weixin_tpl_dir', '__return_empty_string' );

/**
 * 主题初始化
**/
function bls_vr_setup() {
    load_theme_textdomain( 'bls_vr', bls_vr_url('languages') );
	add_theme_support( 'post-thumbnails' );
    //我们要求微信模式下强行全自动登录/注册+登录
    bls_vr_auto_login();
    //强制不显示前台工具栏
    add_filter('show_admin_bar', '__return_false');
}
add_action( 'after_setup_theme', 'bls_vr_setup' );

/**
 * 返回浏览次数（int)
 * $plus 增加一次，默认不增加
 * $post_id 文章ID，默认$post->ID
**/
if(!function_exists('bls_vr_view')):
function bls_vr_view($plus=false, $post_id=false){
    $post_view = 0;
    if(!$post_id){
        global $post;
        $post_id = isset($post->ID) ? $post->ID : false;
    }
    if($post_id){
        $post_meta = get_post_meta($post_id);
        if( isset($post_meta["view"]) ){
            $post_view = current($post_meta["view"]);
            if($plus){
                $post_view ++;
                update_post_meta($post_id, "view", $post_view);
            }
        } else {
            $post_view ++;
            update_post_meta($post_id, "view", $post_view);
        }
    }
    return $post_view;
}
endif;

/**
 * 返回浏览纪录（array)
 * $action 执行动作，默认为给用户增加一条文章浏览纪录
 * $post_id 文章ID，默认$post->ID
 * $user_id 用户ID，默认$current_user->ID
**/
if(!function_exists('bls_vr_record')):
function bls_vr_record($action="plus", $post_id=false, $user_id=false){
    $timestamp = current_time("timestamp");
    $record = array();
    if(!$post_id){
        global $post;
        $post_id = isset($post->ID) ? $post->ID : false;
    }
    if(!$user_id){
        global $current_user;
        $user_id = isset($current_user->ID) ? $current_user->ID : false;
    }
    //先查询session
    $record = isset($_SESSION["record"]) ? json_decode(stripslashes($_SESSION["record"]),true) : array();
    if($user_id){
        $myrecord = get_user_meta($user_id, "record", true);
        //合并session
        $record = array_merge((array)$record, (array)$myrecord);
        if($post_id){
            switch($action){
                default:
                case "plus":
                    if(isset($record[$post_id])){
                        $record[$post_id] = array();
                    }
                    $record[$post_id][] = $timestamp;
                break;
                case "minus":
                    if(isset($record[$post_id])){
                        unset($record[$post_id]);
                    }
                break;
                case "reset":
                    $record = array();
                break;
            }
            update_user_meta($user_id, "record", $record);
        }
    } else {
        //session只增加
        if(isset($record_arr[$post_id])){
            $record_arr[$post_id] = array();
        }
        $record_arr[$post_id][] = $timestamp;
    }
    //写入session
    $_SESSION["record"] = json_encode($record);
    return $record;
}
endif;

/**
 * 修改显示的数量
**
function bls_vr_posts_per_page($query){
    $page_view = 9;//每页显示9个
    if ( $query->is_tax()){
        $query->set('posts_per_page', $page_view);
    }
}
add_action( 'pre_get_posts', 'bls_vr_posts_per_page' );

/**
 * 翻页条
**/
if(!function_exists('bls_vr_pages')):
function bls_vr_pages($p=5){
    global $wp_query;
    $max_page = $wp_query->max_num_pages;
    $paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
    if ( $max_page > 1 ){
        echo '
    <nav class="pager">
        <ul class="pagination">';
        if ( $paged > 1 ) bls_vr_pages_link( $paged - 1, __('&laquo; Previous'),__('&laquo; Previous') );
        if ( $paged > $p + 1 ) bls_vr_pages_link( 1, __('First page') );
        if ( $paged > $p + 2 ) echo '<li class="disabled"><span>...</span></li>';
        for( $i = $paged - $p; $i <= $paged + $p; $i++ ) {
            if ( $i > 0 && $i <= $max_page ) $i == $paged ? print "<li class=\"active\"><span>{$i}</span></li>" : bls_vr_pages_link( $i );
        }
        if ( $paged < $max_page - $p - 1 ) echo '<li class="disabled"><span>...</span></li>';
        if ( $paged < $max_page - $p ) bls_vr_pages_link( $max_page, 'Last page' );
        if ( $paged < $max_page ) bls_vr_pages_link( $paged + 1, __('Next &raquo;'), __('Next &raquo;') );
        echo '
        </ul>
    </nav>';
    }
}
endif;

/**
 * 翻页条链接
**/
if(!function_exists('bls_vr_pages_link')):
    function bls_vr_pages_link( $i, $title = '', $linktype = '' ){
        if ( $title == '' ) $title = "The {$i} page";
        if ( $linktype == '' ) { $linktext = $i; } else { $linktext = $linktype; }
        echo "<li><a href='", esc_html( get_pagenum_link( $i ) ), "' title='{$title}'>{$linktext}</a></li>";
    }
endif;

/**
 * 设置商品页面模板
**/
add_filter( 'template_include', 'bls_vr_shop_page_template', 100);
function bls_vr_shop_page_template( $template ) {
    //分类页
    if(!wp_is_mobile() && is_tax( 'mall' )){
        $template = get_home_template();
    }
    //商品页
    if(!wp_is_mobile() && is_singular( 'goods' )){
        $template = get_home_template();
    }
    return $template;
}

/**
 * 主题输出样式脚本
**/
add_action('wp_enqueue_scripts', 'bls_vr_style_scripts' );
function bls_vr_style_scripts(){
    $debug  = WP_DEBUG ? "" : ".min";
    //先注册全部的样式
    wp_register_style( 'weui', bls_vr.'css/weui'.$debug.'.css', false, '1.0' );
    wp_register_style( 'bls', bls_vr.'css/iconfont.css', array('weui'), '1.0' );
    wp_register_style( 'animate', bls_vr.'css/animate'.$debug.'.css', false, '1.0' );
    wp_register_style( 'swiper', bls_vr.'css/swiper'.$debug.'.css', array('animate'), '3.1.2' );
    wp_register_style( 'bls_vr', bls_vr.'style.css', array('swiper','bls'), '1.0' );
    wp_register_style( 'html5shiv', '//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js', array(), '3.7.2' );
    wp_register_style( 'respond', '//cdn.bootcss.com/respond.js/1.4.2/respond.min.js', array('html5shiv'), '1.4.2' );
    //先注册全部的脚本
    wp_register_script( 'swiper', bls_vr.'js/swiper.jquery'.$debug.'.js', array('jquery'), '3.1.2', true );
    wp_register_script( 'swiper-animate-twice', bls_vr.'js/swiper.animate-twice.min.js', array('swiper'), '1.0', true );
    wp_register_script( 'province-city-county', bls_vr.'js/province-city-county-json.min.js', array('jquery'), '1.0', true );
    wp_register_script( 'lazyload', bls_vr.'js/lazyload'.$debug.'.js', array('jquery'), '3.1.2', true );
    wp_register_script( 'cookie', bls_vr.'js/jquery.cookie.js', array('jquery'), '1.4.1', true );
    wp_register_script( 'cropit', bls_vr.'js/jquery.cropit.js', array('jquery'), '0.5.1', true );
    wp_register_script( 'bls_vr', bls_vr.'js/main.js', array('swiper-animate-twice','lazyload','cookie'), '3.3.2', true );
    
    
    wp_register_script( 'jweixin', 'http://res.wx.qq.com/open/js/jweixin-1.0.0.js', false, '1.0.0', true );
    if(is_page("user")){
        wp_enqueue_script( 'cropit' );
    } elseif(is_page("user")){
        wp_enqueue_script( 'cropit' );
    }
    //默认加载的样式脚本
    wp_enqueue_style( 'bls_vr' );
    wp_enqueue_script( 'bls_vr' );
    //低版本IE样式脚本
	wp_style_add_data( 'html5shiv', 'conditional', 'lt IE 9' );
	wp_style_add_data( 'respond', 'conditional', 'lt IE 9' );
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
    $("#load-in").click(function(e) {
        $(this).hide();
    }).fadeOut(3000, function(){
        //IE8以下版本不能正常浏览本站点
        if(navigator.userAgent.indexOf("MSIE 6.0")>0 || navigator.userAgent.indexOf("MSIE 7.0")>0) {
            $.toptips("请升级浏览器！或者使用非IE浏览器访问！");
        }
    });
    $("a").bind("click", function(){
        $("#load-in").show();
    });
    $("a[role=free]").unbind("click");
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
add_action("wp_head","bls_vr_head_scripts",100);
function bls_vr_head_scripts(){
?>
<script>
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "//hm.baidu.com/hm.js?e271929b73a305d4fccad8a60563122e";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();
</script>
<?php
}

/**
 * 输出bootstrap需要的菜单dom结构
**/
if(!class_exists('wp_bootstrap_navwalker')):
class wp_bootstrap_navwalker extends Walker_Nav_Menu {
	/**
	 * @see Walker::start_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "\n$indent<ul role=\"menu\" class=\" dropdown-menu\">\n";
	}
	/**
	 * @see Walker::start_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param int $current_page Menu item ID.
	 * @param object $args
	 */
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
		/**
		 * Dividers, Headers or Disabled
		 * =============================
		 * Determine whether the item is a Divider, Header, Disabled or regular
		 * menu item. To prevent errors we use the strcasecmp() function to so a
		 * comparison that is not case sensitive. The strcasecmp() function returns
		 * a 0 if the strings are equal.
		 */
		if ( strcasecmp( $item->attr_title, 'divider' ) == 0 && $depth === 1 ) {
			$output .= $indent . '<li role="presentation" class="divider">';
		} else if ( strcasecmp( $item->title, 'divider') == 0 && $depth === 1 ) {
			$output .= $indent . '<li role="presentation" class="divider">';
		} else if ( strcasecmp( $item->attr_title, 'dropdown-header') == 0 && $depth === 1 ) {
			$output .= $indent . '<li role="presentation" class="dropdown-header">' . esc_attr( $item->title );
		} else if ( strcasecmp($item->attr_title, 'disabled' ) == 0 ) {
			$output .= $indent . '<li role="presentation" class="disabled"><a href="#">' . esc_attr( $item->title ) . '</a>';
		} else {
			$class_names = $value = '';
			$classes = empty( $item->classes ) ? array() : (array) $item->classes;
			$classes[] = 'menu-item-' . $item->ID;
			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
			if ( $args->has_children )
				$class_names .= ' dropdown';
			if ( in_array( 'current-menu-item', $classes ) )
				$class_names .= ' active';
			$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';
			$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
			$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';
			$output .= $indent . '<li' . $id . $value . $class_names .'>';
			$atts = array();
			$atts['title']  = ! empty( $item->title )	? $item->title	: '';
			$atts['target'] = ! empty( $item->target )	? $item->target	: '';
			$atts['rel']    = ! empty( $item->xfn )		? $item->xfn	: '';
			// If item has_children add atts to a.
			if ( $args->has_children && $depth === 0 ) {
				$atts['href']   		= '#';
				$atts['data-toggle']	= 'dropdown';
				$atts['class']			= 'dropdown-toggle';
				$atts['aria-haspopup']	= 'true';
			} else {
				$atts['href'] = ! empty( $item->url ) ? $item->url : '';
			}
			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args );
			$attributes = '';
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
					$attributes .= ' ' . $attr . '="' . $value . '"';
				}
			}
			$item_output = $args->before;
			/*
			 * Glyphicons
			 * ===========
			 * Since the the menu item is NOT a Divider or Header we check the see
			 * if there is a value in the attr_title property. If the attr_title
			 * property is NOT null we apply it as the class name for the glyphicon.
			 */
			if ( ! empty( $item->attr_title ) )
				$item_output .= '<a'. $attributes .'><span class="glyphicon ' . esc_attr( $item->attr_title ) . '"></span>&nbsp;';
			else
				$item_output .= '<a'. $attributes .'>';
			$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
			$item_output .= ( $args->has_children && 0 === $depth ) ? ' <span class="caret"></span></a>' : '</a>';
			$item_output .= $args->after;
			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
	}
	/**
	 * Traverse elements to create list from elements.
	 *
	 * Display one element if the element doesn't have any children otherwise,
	 * display the element and its children. Will only traverse up to the max
	 * depth and no ignore elements under that depth.
	 *
	 * This method shouldn't be called directly, use the walk() method instead.
	 *
	 * @see Walker::start_el()
	 * @since 2.5.0
	 *
	 * @param object $element Data object
	 * @param array $children_elements List of elements to continue traversing.
	 * @param int $max_depth Max depth to traverse.
	 * @param int $depth Depth of current element.
	 * @param array $args
	 * @param string $output Passed by reference. Used to append additional content.
	 * @return null Null on failure with no changes to parameters.
	 */
	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
        if ( ! $element )
            return;
        $id_field = $this->db_fields['id'];
        // Display this element.
        if ( is_object( $args[0] ) )
           $args[0]->has_children = ! empty( $children_elements[ $element->$id_field ] );
        parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }
	/**
	 * Menu Fallback
	 * =============
	 * If this function is assigned to the wp_nav_menu's fallback_cb variable
	 * and a manu has not been assigned to the theme location in the WordPress
	 * menu manager the function with display nothing to a non-logged in user,
	 * and will add a link to the WordPress menu manager if logged in as an admin.
	 *
	 * @param array $args passed from the wp_nav_menu function.
	 *
	 */
	public static function fallback( $args ) {
		if ( current_user_can( 'manage_options' ) ) {
			extract( $args );
			$fb_output = null;
			if ( $container ) {
				$fb_output = '<' . $container;
				if ( $container_id )
					$fb_output .= ' id="' . $container_id . '"';
				if ( $container_class )
					$fb_output .= ' class="' . $container_class . '"';
				$fb_output .= '>';
			}
			$fb_output .= '<ul';
			if ( $menu_id )
				$fb_output .= ' id="' . $menu_id . '"';
			if ( $menu_class )
				$fb_output .= ' class="' . $menu_class . '"';
			$fb_output .= '>';
			$fb_output .= '<li><a href="' . admin_url( 'nav-menus.php' ) . '">Add a menu</a></li>';
			$fb_output .= '</ul>';
			if ( $container )
				$fb_output .= '</' . $container . '>';
			echo $fb_output;
		}
	}
}
endif;

/**
 * 返回用户的IP
**/
if(!function_exists('bls_vr_ip')):
function bls_vr_ip(){
    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'),'unknown')){ 
        return getenv('HTTP_CLIENT_IP'); 
    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'),'unknown')){ 
        return getenv('HTTP_X_FORWARDED_FOR'); 
    } elseif(getenv('REMOTE_ADDR')&&strcasecmp(getenv('REMOTE_ADDR'),'unknown')){ 
        return getenv('REMOTE_ADDR'); 
    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'],'unknown')){ 
        return $_SERVER['REMOTE_ADDR']; 
    }
    return "";
}
endif;

/**
 * 文件写入权限检查
 * 默认的php函数is_writable不能在Win32 NTFS环境下工作
**/
if(!function_exists('is_writeable_ACLSafe')):
function is_writeable_ACLSafe($path) {
	if ($path{strlen($path)-1}=='/')
		return is_writeable_ACLSafe($path.uniqid(mt_rand()).'.tmp');
	else if (is_dir($path))
		return is_writeable_ACLSafe($path.'/'.uniqid(mt_rand()).'.tmp');
	$rm = file_exists($path);
	$f = @fopen($path, 'a');
	if ($f===false)
		return false;
	fclose($f);
	if (!$rm)
		unlink($path);
	return true;
}
endif;

/**
 * 文件wp-config.php的写入、更新行操作
 * 写义缓存全局常量$key=$val
 * $val为空值则删除$key
**/
if(!function_exists('update_constant')):
function update_constant($key="",$val=""){
    if(empty($key)) return false;
    //我们要求换行全部替换为<br />
    $val = is_array($val) ? array_map("nl2br",$val) : nl2br($val);
    $str = array("\r\n", "\n", "\r");
    $replace = '';
    $val=str_replace($str, $replace, $val);

    if ( defined( $key ) && constant( $key ) == maybe_serialize($val) ) return true;
    $config = file_exists(ABSPATH.'wp-config.php') ? ABSPATH.'wp-config.php' : dirname(ABSPATH).'/wp-config.php';
	if ( @is_file( $config ) == false ) {
		return false;
	}
	if (!is_writeable_ACLSafe($config)) {
        //如果获取失败则刷新页面
        add_action('admin_notices', 'bls_vr_update_constant_error_notices');
        function bls_vr_update_constant_error_notices(){
            echo '<div class="wrap nosubsub"><div class="error below-h2"><p><strong> 请修改'.home_url('/wp-config.php').'为可写权限! </strong></p></div></div>';
        }
		return false;
	}
    $lines = file($config);
    $new = "define('{$key}', '".maybe_serialize($val)."');";
    $old = "define('{$key}'";
    //查找是否存在
    $found = false;
	foreach( (array)$lines as $line ) {
	 	if ( strpos($line,$old) !== false) {
			$found = true;
            break;
		}
	}
    if($found){
        //替换
        $file = fopen($config, 'w');
        $done = false;
        foreach( (array)$lines as $line ) {
            if ( strpos($line,$old) !== false && strpos($line,"//from theme") !== false && !$done) {
                //如果$val是空值，我们会直接删除
                if($val!==""){
                    //否则修改为新的值
                    fputs($file, "$new //from theme\n");
                }
                $done = true;
            } elseif ( strpos($line,$old) !== false && $done) {
                //如果$val是空值，我们会直接删除 
            } else {
                fputs($file, $line);
            }
        }
        fclose($file);
    } else {
        //插入
	    $file = fopen($config, 'w');
        $done = false;
        foreach( (array)$lines as $line ) {
            if ( strpos($line,"define") === false || $done ) {
                fputs($file, $line);
            } else {
                fputs($file, "$new //from theme\n");
                fputs($file, $line);
                $done = true;
            }
        }
	    fclose($file);
    }
	return true;
}
endif;

/*
 * 查询bls_vr全局变量
**/
if(!function_exists('get_bls_vr_option')):
function get_bls_vr_option($key,$default=""){
    $bls_vr_data = defined("bls_vr_data") ? maybe_unserialize(bls_vr_data,true) : array();
    if(!is_array($bls_vr_data)) $bls_vr_data = array();
    $str = isset($bls_vr_data[$key]) ? maybe_unserialize($bls_vr_data[$key]) : $default;
    if(is_array($str))
        return array_map("br2nl",$str);
    else
        return str_replace("<br />","\n",$str);
}
endif;

/*
 * 查询bls_vr全局变量并直接输出
**/
if(!function_exists('bls_vr_option')):
function bls_vr_option($key,$default=""){
	echo get_bls_vr_option($key,$default);
}
endif;

/*
 * 把换行标签换成换行符
**/
if(!function_exists('br2nl')):
function br2nl($str=""){
    return str_replace("<br />","\n",$str);
}
endif;

/**
 * 加密
**/
if(!function_exists('bls_vr_encode')):
function bls_vr_encode($string = '', $skey = 'bls_vr') {
    return str_replace('=', 'O0O0O', base64_encode(base64_encode($skey).trim($string)));
}
endif;

/**
 * 解密
**/
if(!function_exists('bls_vr_decode')):
function bls_vr_decode($string = '', $skey = 'bls_vr') {
    $encode = base64_encode($skey);
    return substr(base64_decode(str_replace('O0O0O', '=', trim($string))), strlen($encode));
}
endif;

/**
 * 验证
**/
if(!function_exists('bls_vr_verify')):
function bls_vr_verify($string = '', $encode = '', $skey = 'bls_vr') {
    return (bls_vr_decode($encode, $skey)==trim($string) ? true : false);
}
endif;

/**
 * 给管理者加上配置功能
**/
add_action('admin_menu', 'bls_vr_menu');
function bls_vr_menu() {
	add_theme_page('配置', '配置', 'edit_theme_options', 'bls_vr-settings', 'bls_vr_settings_fn');
}
function bls_vr_settings_fn(){
    global $wpdb;
    wp_enqueue_media();
    $timestamp = current_time("timestamp");
    $ajax_none = wp_create_nonce( "bls_vr_nonce" );
    $str = "";
    $bls_vr_settings_attr = array (
        array(
            "title" => __( '了解爱家湘能', 'bls_vr' ),
            "desc"  => __( '输入了解爱家湘能跳转页面的ID', 'bls_vr' ),
            "name"  => "about-us",
            "type"  => "text",
            "std"   => '您好，欢迎来到%s'
        ),
        array(
            "title" => __( '400电话', 'bls_vr' ),
            "desc"  => __( '请填写400客服电话', 'bls_vr' ),
            "name"  => "400",
            "type"  => "text",
            "std"   => ''
        ),
        array(
            "title" => __( '首页产品分类', 'bls_vr' ),
            "desc"  => __( '输入产品分类的ID,多个使用英文逗号隔开', 'bls_vr' ),
            "name"  => "grids_ids",
            "type"  => "text",
            "std"   => ''
        ),
        array(
            "title" => __( '地暖产品分类', 'bls_vr' ),
            "desc"  => __( '输入产品分类的ID,多个使用英文逗号隔开', 'bls_vr' ),
            "name"  => "floor_heating_ids",
            "type"  => "text",
            "std"   => ''
        ),
        array(
            "title" => __( '暗装暖气片分类', 'bls_vr' ),
            "desc"  => __( '输入产品分类的ID,多个使用英文逗号隔开', 'bls_vr' ),
            "name"  => "heating_ids",
            "type"  => "text",
            "std"   => ''
        ),
        array(
            "title" => __( '明装暖气片产品分类', 'bls_vr' ),
            "desc"  => __( '输入产品分类的ID,多个使用英文逗号隔开', 'bls_vr' ),
            "name"  => "radiator_ids",
            "type"  => "text",
            "std"   => ''
        ),
        array(
            "title" => __( '混装产品分类', 'bls_vr' ),
            "desc"  => __( '输入产品分类的ID,多个使用英文逗号隔开', 'bls_vr' ),
            "name"  => "mix_heating_ids",
            "type"  => "text",
            "std"   => ''
        ),
        array(
            "title" => __( '空调产品分类', 'bls_vr' ),
            "desc"  => __( '输入产品分类的ID,多个使用英文逗号隔开', 'bls_vr' ),
            "name"  => "air_ids",
            "type"  => "text",
            "std"   => ''
        ),
        array(
            "title" => __( '新风产品分类', 'bls_vr' ),
            "desc"  => __( '输入产品分类的ID,多个使用英文逗号隔开', 'bls_vr' ),
            "name"  => "wind_ids",
            "type"  => "text",
            "std"   => ''
        ),
        array(
            "title" => __( '腾讯地图key', 'bls_vr' ),
            "desc"  => __( '请访问获取', 'bls_vr' ).'<a href="http://lbs.qq.com/key.html" target="_blank"><code>http://lbs.qq.com/key.html</code></a>',
            "name"  => "qq_lbs",
            "type"  => "text",
            "std"   => ''
        )
    );
    $bls_vr_settings_attr = apply_filters("bls_vr_settings_attr", $bls_vr_settings_attr);
    //我们把所有变量保存到 wp-config.php 的 bls_vr_settings_attr 常量中来减少数据库读写次数
    $bls_vr_data = defined("bls_vr_data") ? maybe_unserialize(bls_vr_data) : array();
    if(!is_array($bls_vr_data)) $bls_vr_data = array();
    //初始化&升级时执行
    if(!isset($bls_vr_data["version"]) || (isset($bls_vr_data["version"]) && version_compare($bls_vr_data["version"], bls_vr_ver) >= 0)){
        //更新版本值
        $bls_vr_data["version"] = bls_vr_ver;
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
        update_constant("bls_vr_data",$bls_vr_data);
        wp_redirect(admin_url('themes.php?page=bls_vr-settings&settings-updated=true'));
        exit;
	}    
?>
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
    <form novalidate method="post" enctype="multipart/form-data" action="<?php echo admin_url("admin.php?page=bls_vr-settings"); ?>">
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
                    case "textarea":
            ?>
                <tr>
                    <th scope="row"><label for="<?php echo $name; ?>"><?php echo $val["title"]; ?></label></th>
                    <td><textarea name="<?php echo $name; ?>" id="<?php echo $name; ?>" class="regular-text"><?php esc_attr_e($value); ?></textarea>
                    <p class="description" id="<?php echo $name; ?>-description"><?php echo $val["desc"]; ?></p></td></td>
                </tr>
            <?php
                    break;
                    case "select":
            ?>
                <tr>
                    <th scope="row"><label for="<?php echo $name; ?>"><?php echo $val["title"]; ?></label></th>
                    <td><select name="<?php echo $name; ?>" id="<?php echo $name; ?>"><?php 
                    foreach($val["options"] as $option_value=>$option_title){
                    ?><option<?php if($value==$option_value) echo ' selected="selected"'; ?> value="<?php echo $option_value; ?>"><?php echo $option_title; ?></option><?php
                    } ?></select>
                    <p class="description" id="<?php echo $name; ?>-description"><?php echo $val["desc"]; ?></p></td></td>
                </tr>
            <?php
                    break;
                    case "radio":
            ?>
                <tr>
                    <th scope="row"><label for="<?php echo $name; ?>"><?php echo $val["title"]; ?></label></th>
                    <td><fieldset>
                            <legend class="screen-reader-text"><span><?php echo $val["title"]; ?></span></legend>
                            <?php 
                    foreach($val["options"] as $option_value=>$option_title){
                    ?>
                            <label title="Y年n月j日">
                                <input type="radio" name="date_format" value="<?php echo $option_value; ?>"<?php if($value==$option_value) echo ' checked="checked"'; ?>>
                                <?php echo $option_title; ?></label>
                            <br>
                            <option<?php if($value==$option_value) echo ' selected="selected"'; ?> value="<?php echo $option_value; ?>"><?php echo $option_title; ?></option><?php
                    } ?></select>
                    <p class="description" id="<?php echo $name; ?>-description"><?php echo $val["desc"]; ?></p></td></td>
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
<?php
}

/**
 * 用户提醒
**/
if(!function_exists('bls_vr_tips')):
function bls_vr_tips(){
    global $wpdb,$current_user;
    if($current_user->ID>0){
        $user_info = bls_weixin_user_meta();
        echo '
            <div class="weui_tips">
                <span class="tips bls icon-tixing"></span>
                <div class="swiper-container">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide"><a href="air.html" title="">我没有爱情，只有回忆我没有爱情，只有回忆我没有爱情，只有回忆我没有爱情，只有回忆我没有爱情，只有回忆</a></div>
                        <div class="swiper-slide"><a href="air.html" title="">我喜欢你，是我独家的回忆</a></div>
                    </div>
                </div>
            </div>';
    }
}
endif;

/**
 * 相册样式
**/
add_shortcode('bls_vr_gallery', 'bls_vr_gallery_shortcode');
if(!function_exists('bls_vr_gallery_shortcode')):
function bls_vr_gallery_shortcode( $attr ) {
    //bls_vr_gallery columns="1" link="none" size="full" ids="879,880,881,882,883,884,885"
	global $wpdb,$post;
	$atts = shortcode_atts( array(
		'order'      => 'ASC',
		'orderby'    => 'order by post_id',
		'columns'    => 3,
		'size'       => 'thumbnail',
		'ids'        => ""
	), $attr);
    $ids = preg_split("/\D/", $atts["ids"]);
    $results = $wpdb->get_results("select post_id,meta_value from $wpdb->postmeta where meta_key='_wp_attachment_metadata' and post_id in (".implode(",", $ids).") ");
    $size = $atts["size"];
    foreach($ids as $key=>$id){
        foreach($results as $result){
            if($id == $result->post_id){
                $metadata = maybe_unserialize( $result->meta_value );
                $file = isset($metadata["sizes"][$size]) ? $metadata["sizes"][$size]["file"] : $metadata["file"];
                echo '<img class="lazy" data-original="'.WP_CONTENT_URL.'/uploads/'.$file.'" alt="'.$post->post_title.'">';
            }
        }
    }
}
endif;

/**
 * 相册样式
**/
if(!function_exists('bls_vr_album')):
function bls_vr_album( $ids="", $size="full", $echo=true ) {
	global $wpdb,$post;
    $str = "";
    if($ids){
        $ids = preg_split("/\D/", $ids);
        if(count($ids)>0){
            $results = $wpdb->get_results("select post_id,meta_value from $wpdb->postmeta where meta_key='_wp_attachment_metadata' and post_id in (".implode(",", $ids).") ");
            foreach($ids as $key=>$id){
                foreach($results as $result){
                    if($id == $result->post_id){
                        $metadata = maybe_unserialize( $result->meta_value );
                        $file = isset($metadata["sizes"][$size]) ? $metadata["sizes"][$size]["file"] : $metadata["file"];
                        $str .= '
                    <div class="swiper-slide">
                        <img src="'.WP_CONTENT_URL.'/uploads/'.$file.'" alt="'.wp_kses($post->post_title, "").'" class="swiper-lazy">
                        <div class="swiper-lazy-preloader"></div>
                    </div>';
                    }
                }
            }
        }
    }
    if($echo){
        echo $str;
    } else {
        return $str;
    }
}
endif;

/**
 * 给文章计数
**/
if(!function_exists('bls_vr_count')):
function bls_vr_count( $post_id=0, $count="view", $number=false, $echo=false ) {
    if(!$post_id){
        global $post;
        $post_id = $post->ID;
    }
    $post_id = is_object($post_id) ? $post_id->ID : intval($post_id);
    $total = 0;
    if($post_id > 0){
        $total = get_post_meta($post_id, $count, true);
        if(!is_numeric($total)){
            $total = 0;
        }
        if($number){
            $total = $number>0 ? $total+1 : max(0, $total-1);
            update_post_meta($post_id, $count, $total);
        }
    }
    if($echo){
        echo $total;
    } else {
        return $total;
    }
}
endif;

/**
 * 修改用户喜欢
 * $status  1喜欢，-1不喜欢，0搜索结果
**/
if(!function_exists('bls_vr_user_like')):
function bls_vr_user_like( $user_id=false, $post_id=false, $status=false ) {
    if(!$user_id){
        global $current_user;
        $user_id = $current_user->ID;
    }
    if(!$post_id){
        global $post;
        $post_id = $post->ID;
    }
    $user_id = is_object($user_id) ? $user_id->ID : intval($user_id);
    $post_id = is_object($post_id) ? $post_id->ID : intval($post_id);
    $liked = -1;
    if($user_id > 0 && $post_id > 0){
        $like_arr = $user_id > 0 ? get_user_meta($user_id, "like", true) : array();
        if(!is_array($like_arr)){
            $like_arr = array();
        }
        if($status){
            $liked = $status;
            if(!isset($like_arr[$post_id]) || (isset($like_arr[$post_id]) && $like_arr[$post_id]!=$liked)){
                $like_arr[$post_id] = $liked;
                update_user_meta($user_id, "like", $like_arr);
                bls_vr_count( $post_id, "like", $liked );
            }
        } else {
            $liked = isset($like_arr[$post_id]) ? $like_arr[$post_id] : $liked;
        }
    }
    return $liked;
}
endif;

/**
 * 输出商品的价格
 * $post_id 商品ID
 * $type 0 折扣价 1 市场价
**/
if(!function_exists('bls_vr_price')):
function bls_vr_price($post_id=false, $type=0) {
    if(is_object($post_id)) $post_id = $post_id->ID;
    if(!$post_id)  return false;
    $result             = "-";
    //优先读取cookie
    $area               = isset($_COOKIE["area"]) ? round($_COOKIE["area"], 2) : false; //总面积
    $area_penthouse     = isset($_COOKIE["area_penthouse"]) ? round($_COOKIE["area_penthouse"], 2) : 0; //挑空面积
    $area_top_floor     = isset($_COOKIE["area_top_floor"]) ? round($_COOKIE["area_top_floor"], 2) : 0; //顶层面积
    $area_basement      = isset($_COOKIE["area_basement"]) ? round($_COOKIE["area_basement"], 2) : 0; //地下室面积
    $area_east_west     = isset($_COOKIE["area_east_west"]) ? round($_COOKIE["area_east_west"], 2) : 0; //东西面有外墙
    $area_toilet        = isset($_COOKIE["area_toilet"]) ? round($_COOKIE["area_toilet"], 2) : 0; //卫生间面积
    $room_terminal      = isset($_COOKIE["room_terminal"]) ? intval($_COOKIE["room_terminal"]) : false; //房间末端数量
    $non_room_terminal  = isset($_COOKIE["non_room_terminal"]) ? intval($_COOKIE["non_room_terminal"]) : 0; //厨、卫、衣帽间末端数量
    $heating            = isset($_COOKIE["heating"]) ? $_COOKIE["heating"] : "off"; //有无采暖区域
    $windows            = isset($_COOKIE["windows"]) ? $_COOKIE["windows"] : "on"; //有无采用平开窗
    $post_info          = get_post_meta($post_id);
    $mode               = isset($post_info["mode"]) ? $post_info["mode"][0] : "";//类型 0普通 1暗装暖气 2明装暖气 3地暖 4暖气片 5新风 6空调
    $stock              = isset($post_info["stock"]) ? $post_info["stock"][0] : "";//库存
    $price              = isset($post_info["price"]) ? $post_info["price"][0] : "";//市场价格
    $cost               = isset($post_info["cost"]) ? $post_info["cost"][0] : "";//销售单价
    $quotiety           = isset($post_info["quotiety"]) ? $post_info["quotiety"][0] : "";//空调的主机价格系数
    $quotation          = isset($post_info["quotation"]) ? $post_info["quotation"][0] : "";//空调的内机设备价格
    $installation_cost  = isset($post_info["installation_cost"]) ? $post_info["installation_cost"][0] : "";//空调的安装费
    $surcharge          = isset($post_info["surcharge"]) ? $post_info["surcharge"][0] : "";//空调是否需要增加智能控制费用
    $discount           = isset($post_info["discount"]) ? $post_info["discount"][0] : "";//折扣 (总价 x 折扣 = 显示的售价)
    $basicarea          = isset($post_info["basicarea"]) ? $post_info["basicarea"][0] : "";//最小面积
    $plus               = isset($post_info["plus"]) ? $post_info["plus"][0] : "";//本产品是否支持厨卫衣帽间
    switch($mode){
        default:
        case 0:
            $result = $type ? $price : $cost;
        break;
        case 1:
            if($area){
                //S=S1+S2+0.3*S3-0.2*S4+S5
                $s = $area * 1 + $area_penthouse * 1 + $area_top_floor * 0.3 - $area_basement * 0.2 + $area_toilet * 1;
                $result = $type ? $price * $s : $cost * $s;
            }
        break;
        case 2:
            if($area){
                $k = $windows=="on" ? 1 : 1.2; //有采用平开窗
                $s1 = max($area, $basicarea, 40); //实际采暖面积必须大于等于40平米
                //S=S1+0.2*S2+0.2*S3-0.1*S4+S5
                $s = $s1 * 1 + $area_penthouse * 0.2 + $area_top_floor * 0.2 - $area_basement * 0.1 + $area_toilet * 1;
                $result = $type ? $price * $s * $k : $cost * $s * $k;
            }
        break;
        case 3:
            if($area){
                //S=S1+0.2*S2+0.2*S3-0.1*S4+S5
                $s = $area * 1 + $area_penthouse * 0.2 + $area_top_floor * 0.2 - $area_basement * 0.1 + $area_toilet * 1;
                $result = $type ? $price * $s : $cost * $s;
            }
        break;
        case 4:
            if($area){
                //S=S1+S2+0.3*S3-0.2*S4+S5
                $s = $area * 1 + $area_penthouse * 1 + $area_top_floor * 0.3 - $area_basement * 0.2 + $area_toilet * 1;
                $result = $type ? $price * $s : $cost * $s;
            }
        break;
        case 5:
            $area = max($area, $basicarea, 40); //实际新风面积必须大于等于40平米
            $result = $stock>0 ? ($type ? $price : $cost) : ($area ? ($type ? $price * $area : $cost * $area) : $result);
        break;
        case 6:
            if($area && $room_terminal){
                $b = $heating=="on" ? 200 : 220; //有无采暖区域
                $plus = $plus ? $non_room_terminal : 0; //是否支持厨卫衣帽间
                $s1 = max($area, $basicarea, 50); //实际采暖面积必须大于等于50平米
                //S=S1+S2+S3+S4+S5
                $s = $s1 * 1 + $area_east_west * 0.1 + $area_top_floor * 0.1 - $area_basement * 0.2 + $area_penthouse * 0.3;
                //N1=N+S/110+PLUS [如果支持厨卫衣帽间]  (S/110取整加1）
                $n1 = $room_terminal + ceil($s*0.00909091) + $plus;
                //R=S*A*B+N*C+N1*D+E
                if($quotiety>0 && $quotation>0 && $installation_cost>0){
                    $r = $s * $quotiety * $b + ($room_terminal + $plus) * $quotation + $n1 * $installation_cost + $surcharge;
                    $result = $type ? $r : $r * $discount * 0.1;
                }
            }
        break;
    }
    return $result;
}
endif;

/**
 * 输出商品的信息
 * $post_id 商品ID
**/
if(!function_exists('bls_vr_info')):
function bls_vr_info($post=false) {
    if(is_numeric($post)) $post = get_post($post);
    $post_id = isset($post->ID) ? $post->ID : false;
    if(!$post_id) return false;
    $result             = array(
                              "title" => array(
                                  "key" => "商品名称",
                                  "val" => $post->post_title,
                                  "unit"=> ""
                              )
                          );
    //优先读取cookie
    $number             = isset($_COOKIE["number"]) ? intval($_COOKIE["number"]) : false; //数量
    $sku                = isset($_COOKIE["order"]) ? intval($_COOKIE["order"]) : $post_id; //sku
    $area               = isset($_COOKIE["area"]) ? round($_COOKIE["area"], 2) : false; //总面积
    $area_penthouse     = isset($_COOKIE["area_penthouse"]) ? round($_COOKIE["area_penthouse"], 2) : 0; //挑空面积
    $area_top_floor     = isset($_COOKIE["area_top_floor"]) ? round($_COOKIE["area_top_floor"], 2) : 0; //顶层面积
    $area_basement      = isset($_COOKIE["area_basement"]) ? round($_COOKIE["area_basement"], 2) : 0; //地下室面积
    $area_east_west     = isset($_COOKIE["area_east_west"]) ? round($_COOKIE["area_east_west"], 2) : 0; //东西面有外墙
    $area_toilet        = isset($_COOKIE["area_toilet"]) ? round($_COOKIE["area_toilet"], 2) : 0; //卫生间面积
    $room_terminal      = isset($_COOKIE["room_terminal"]) ? intval($_COOKIE["room_terminal"]) : false; //房间末端数量
    $non_room_terminal  = isset($_COOKIE["non_room_terminal"]) ? intval($_COOKIE["non_room_terminal"]) : 0; //厨、卫、衣帽间末端数量
    $heating            = isset($_COOKIE["heating"]) ? $_COOKIE["heating"] : "off"; //有无采暖区域
    $windows            = isset($_COOKIE["windows"]) ? $_COOKIE["windows"] : "on"; //有无采用平开窗
    $post_info          = get_post_meta($post_id);
    $stock              = isset($post_info["stock"]) ? $post_info["stock"][0] : "";//库存
    $unit               = isset($post_info["unit"]) ? $post_info["unit"][0] : "";//单位
    $mode               = isset($post_info["mode"]) ? $post_info["mode"][0] : "";//类型 0普通 1暗装暖气 2明装暖气 3地暖 4暖气片 5新风 6空调
    $stock              = isset($post_info["stock"]) ? $post_info["stock"][0] : "";//库存
    $price              = isset($post_info["price"]) ? $post_info["price"][0] : "";//市场价格
    $cost               = isset($post_info["cost"]) ? $post_info["cost"][0] : "";//销售单价
    $payment            = isset($post_info["payment"]) ? $post_info["payment"][0] : 3000;//预付金
    $quotiety           = isset($post_info["quotiety"]) ? $post_info["quotiety"][0] : "";//空调的主机价格系数
    $quotation          = isset($post_info["quotation"]) ? $post_info["quotation"][0] : "";//空调的内机设备价格
    $installation_cost  = isset($post_info["installation_cost"]) ? $post_info["installation_cost"][0] : "";//空调的安装费
    $surcharge          = isset($post_info["surcharge"]) ? $post_info["surcharge"][0] : "";//空调是否需要增加智能控制费用
    $discount           = isset($post_info["discount"]) ? $post_info["discount"][0] : "";//折扣 (总价 x 折扣 = 显示的售价)
    $basicarea          = isset($post_info["basicarea"]) ? $post_info["basicarea"][0] : "";//最小面积
    $plus               = isset($post_info["plus"]) ? $post_info["plus"][0] : "";//本产品是否支持厨卫衣帽间
    $switch_sku         = isset($post_info["switch_sku"]) ? $post_info["switch_sku"][0] : "";//是否开启品类管管理功能
    $sku_arr            = isset($post_info["sku"]) ? maybe_unserialize($post_info["sku"][0]) : array();//SKU
    switch($mode){
        default:
        case 0:
            $result["number"] = array("key"=>"数量", "val" => $number, "unit"=>$unit );
            $result["unit"] = array("key"=>"单位", "val" => $unit, "unit"=>"" );
            if($switch_sku){
                foreach($sku_arr as $key=>$val){
                    if($sku == $val["order"]){
                        $result["title"]["val"] = $val["name"];
                        $price = $val["price"];
                        $cost  = $val["cost"];
                        $stock = $val["number"];
                    }
                }
            }
            $number = min($stock,$number);
            $result["price"] = array("key"=>"全国统一价", "val" => $price * $number, "unit"=>"元");
            $result["cost"] = array("key"=>"限时抢购价", "val" => $cost * $number, "unit"=>"元");
        break;
        case 1:
            $result["area"] = array("key"=>"采暖区域总面积", "val" => $area, "unit"=>"m<sup>2</sup>");
            $result["area_penthouse"] = array("key"=>"有挑空层的房间总面积", "val" => $area_penthouse, "unit"=>"m<sup>2</sup>");
            $result["area_top_floor"] = array("key"=>"位于顶层的房间总面积", "val" => $area_top_floor, "unit"=>"m<sup>2</sup>");
            $result["area_basement"] = array("key"=>"地下室房间总面积", "val" => $area_basement, "unit"=>"m<sup>2</sup>");
            $result["area_toilet"] = array("key"=>"卫生间总面积", "val" => $area_toilet, "unit"=>"m<sup>2</sup>");
            if($area){
                //S=S1+S2+0.3*S3-0.2*S4+S5
                $s = $area * 1 + $area_penthouse * 1 + $area_top_floor * 0.3 - $area_basement * 0.2 + $area_toilet * 1;
                $result["price"] = array("key"=>"全国统一价", "val" => $price * $s, "unit"=>"元");
                $result["cost"] = array("key"=>"限时抢购价", "val" => $cost * $s, "unit"=>"元");
            }
        break;
        case 2:
            $result["area"] = array("key"=>"采暖区域总面积", "val" => $area, "unit"=>"m<sup>2</sup>");
            $result["area_penthouse"] = array("key"=>"有挑空层的房间总面积", "val" => $area_penthouse, "unit"=>"m<sup>2</sup>");
            $result["area_top_floor"] = array("key"=>"位于顶层的房间总面积", "val" => $area_top_floor, "unit"=>"m<sup>2</sup>");
            $result["area_basement"] = array("key"=>"地下室房间总面积", "val" => $area_basement, "unit"=>"m<sup>2</sup>");
            $result["area_toilet"] = array("key"=>"卫生间总面积", "val" => $area_toilet, "unit"=>"m<sup>2</sup>");
            $result["windows"] = array("key"=>"窗户类型", "val" => ($windows=="on" ? "平开窗" : "推拉窗"), "unit"=>"");
            if($area){
                $k = $windows=="on" ? 1 : 1.2; //有采用平开窗
                $s1 = max($area, $basicarea, 40); //实际采暖面积必须大于等于40平米
                //S=S1+0.2*S2+0.2*S3-0.1*S4+S5
                $s = $s1 * 1 + $area_penthouse * 0.2 + $area_top_floor * 0.2 - $area_basement * 0.1 + $area_toilet * 1;
                $result["price"] = array("key"=>"全国统一价", "val" => $price * $s * $k, "unit"=>"元");
                $result["cost"] = array("key"=>"限时抢购价", "val" => $cost * $s * $k, "unit"=>"元");
            }
        break;
        case 3:
            $result["area"] = array("key"=>"采暖区域总面积", "val" => $area, "unit"=>"m<sup>2</sup>");
            $result["area_penthouse"] = array("key"=>"有挑空层的房间总面积", "val" => $area_penthouse, "unit"=>"m<sup>2</sup>");
            $result["area_top_floor"] = array("key"=>"位于顶层的房间总面积", "val" => $area_top_floor, "unit"=>"m<sup>2</sup>");
            $result["area_basement"] = array("key"=>"地下室房间总面积", "val" => $area_basement, "unit"=>"m<sup>2</sup>");
            $result["area_toilet"] = array("key"=>"卫生间总面积", "val" => $area_toilet, "unit"=>"m<sup>2</sup>");
            if($area){
                //S=S1+0.2*S2+0.2*S3-0.1*S4+S5
                $s = $area * 1 + $area_penthouse * 0.2 + $area_top_floor * 0.2 - $area_basement * 0.1 + $area_toilet * 1;
                $result["price"] = array("key"=>"全国统一价", "val" => $price * $s, "unit"=>"元");
                $result["cost"] = array("key"=>"限时抢购价", "val" => $cost * $s, "unit"=>"元");
            }
        break;
        case 4:
            $result["area"] = array("key"=>"采暖区域总面积", "val" => $area, "unit"=>"m<sup>2</sup>");
            $result["area_penthouse"] = array("key"=>"有挑空层的房间总面积", "val" => $area_penthouse, "unit"=>"m<sup>2</sup>");
            $result["area_top_floor"] = array("key"=>"位于顶层的房间总面积", "val" => $area_top_floor, "unit"=>"m<sup>2</sup>");
            $result["area_basement"] = array("key"=>"地下室房间总面积", "val" => $area_basement, "unit"=>"m<sup>2</sup>");
            $result["area_toilet"] = array("key"=>"卫生间总面积", "val" => $area_toilet, "unit"=>"m<sup>2</sup>");
            if($area){
                //S=S1+S2+0.3*S3-0.2*S4+S5
                $s = $area * 1 + $area_penthouse * 1 + $area_top_floor * 0.3 - $area_basement * 0.2 + $area_toilet * 1;
                $result["price"] = array("key"=>"全国统一价", "val" => $price * $s, "unit"=>"元");
                $result["cost"] = array("key"=>"限时抢购价", "val" => $cost * $s, "unit"=>"元");
            }
        break;
        case 5:
            $area = max($area, $basicarea, 40); //实际新风面积必须大于等于40平米
            if($stock>0){
                $number = min($stock,$number);
                $result["number"] = array("key"=>"数量", "val" => $number , "unit"=>$unit);
                $result["unit"] = array("key"=>"单位", "val" => $unit , "unit"=>"");
                $result["price"] = array("key"=>"全国统一价", "val" => $price * $number, "unit"=>"元");
                $result["cost"] = array("key"=>"限时抢购价", "val" => $cost * $number, "unit"=>"元");
            } elseif($area){
                $result["area"] = array("key"=>"新风总面积", "val" => $area, "unit"=>"m<sup>2</sup>");
                $result["price"] = array("key"=>"全国统一价", "val" => $price * $area, "unit"=>"元");
                $result["cost"] = array("key"=>"限时抢购价", "val" => $cost * $area, "unit"=>"元");
            }
        break;
        case 6:
            $result["area"] = array("key"=>"空调总面积", "val" => $area, "unit"=>"m<sup>2</sup>");
            $result["heating"] = array("key"=>"是否安装暖气", "val" => ($heating=="on" ? "有安装" : "尚未安装"), "unit"=>"");
            $result["area_east_west"] = array("key"=>"东西面有外墙的房间总面积", "val" => $area_east_west, "unit"=>"m<sup>2</sup>");
            $result["area_penthouse"] = array("key"=>"有挑空层的房间总面积", "val" => $area_penthouse, "unit"=>"m<sup>2</sup>");
            $result["area_top_floor"] = array("key"=>"位于顶层的房间总面积", "val" => $area_top_floor, "unit"=>"m<sup>2</sup>");
            $result["area_basement"] = array("key"=>"地下室房间总面积", "val" => $area_basement, "unit"=>"m<sup>2</sup>");
            $result["room_terminal"] = array("key"=>"中央空调末端安装数量", "val" => $room_terminal, "unit"=>"m<sup>2</sup>");
            if($plus)
            $result["room_terminal"] = array("key"=>"厨卫衣帽间末端总数", "val" => $room_terminal, "unit"=>"m<sup>2</sup>");
            if($area && $room_terminal){
                $b = $heating=="on" ? 200 : 220; //有无采暖区域
                $plus = $plus ? $non_room_terminal : 0; //是否支持厨卫衣帽间
                $s1 = max($area, $basicarea, 50); //实际采暖面积必须大于等于50平米
                //S=S1+S2+S3+S4+S5
                $s = $s1 * 1 + $area_east_west * 0.1 + $area_top_floor * 0.1 - $area_basement * 0.2 + $area_penthouse * 0.3;
                //N1=N+S/110+PLUS [如果支持厨卫衣帽间]  (S/110取整加1）
                $n1 = $room_terminal + ceil($s*0.00909091) + $plus;
                //R=S*A*B+N*C+N1*D+E
                if($quotiety>0 && $quotation>0 && $installation_cost>0){
                    $r = $s * $quotiety * $b + ($room_terminal + $plus) * $quotation + $n1 * $installation_cost + $surcharge;
                    $result["price"] = array("key"=>"全国统一价", "val" => $r, "unit"=>"元");
                    if($discount<10)
                    $result["cost"] = array("key"=>"限时抢购价", "val" => $r * $discount * 0.1, "unit"=>"元");
                }
            }
        break;
    }
    $result["payment"] = $payment>0 ? array("key"=>"预付", "val" => $payment, "unit"=>"元") : array("key"=>"预付", "val" => $result["cost"]["val"], "unit"=>"元");
    return $result;
}
endif;

/**
 * 微信自动注册登录
 * $register true 自动注册 false 只验证
 * $subscribe true 只限关注用户 false 所有用户
**/
if(!function_exists('bls_vr_auto_login')):
function bls_vr_auto_login($register=true, $subscribe=true) {
    if(is_weixin()){
        if(!function_exists('is_user_logged_in')) require_once (ABSPATH . WPINC . '/pluggable.php');
        //登录前有两种状态
        if(is_user_logged_in()){
            return true;
        } else {
            $protocol   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url        = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $timestamp  = current_time("timestamp");
            $appid      = get_bls_weixin_option("appid",false);
            $secret     = get_bls_weixin_option("appsecret",false);
            if($appid && $secret){
                if(isset($_GET["code"])){
                    $code = $_GET["code"];
                    $remote_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
                    $remote_data = json_decode(wp_remote_retrieve_body(wp_remote_get($remote_url)));
                    if(isset($remote_data->openid)){
                        $openid = $remote_data->openid;
                        //数据获取成功
                        $user_info = bls_weixin_user_meta_by_openid($openid);
                        if($user_info){
                            //已经注册的用户直接登录
                            $user_login = $user_info->user_login;
                            wp_set_current_user($user_info->ID, $user_login);
                            wp_set_auth_cookie($user_info->ID);
                            do_action('wp_login', $user_login);
                            return true;
                        } else {
                            //开启自动注册时
                            if($register){
                                global $wpdb;
                                $user_id = 0;
                                if($subscribe){
                                    $access_token = get_bls_weixin_option("access_token",false);
                                    if($access_token){
                                        $remote_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
                                        $user_date = json_decode(wp_remote_retrieve_body(wp_remote_get($remote_url)));
                                        //有关注微信公众号
                                        if(isset($user_date->subscribe) && $user_date->subscribe>0){
                                            $user_number= get_new_uid();
                                            $user_login = "2-".$user_number;
                                            $user_id    = wp_insert_user(array(
                                                'user_login'    => $user_login,
                                                'user_pass'     => NULL,
                                                'role'          => 'bls_customer'
                                            ));
                                            //写入用户微信基本数据
                                            /*{
                                               "subscribe": 1, 用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息。
                                               "openid": "o6_bmjrPTlm6_2sgVt7hMZOPfL2M", 用户的标识，对当前公众号唯一
                                               "nickname": "Band", 用户的昵称
                                               "sex": 1, 用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
                                               "language": "zh_CN", 用户的语言，简体中文为zh_CN
                                               "city": "广州", 用户所在城市
                                               "province": "广东", 用户所在省份
                                               "country": "中国", 用户所在国家
                                               "headimgurl":  "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/0", 用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。
                                               "subscribe_time": 1382694957,用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间
                                               "unionid": " o6_bmasdasdsad6_2sgVt7hMZOPfL"只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段。
                                               "remark": "",公众号运营者对粉丝的备注，公众号运营者可在微信公众平台用户管理界面对粉丝添加备注
                                               "groupid": 0,用户所在的分组ID（兼容旧的用户分组接口）
                                               "tagid_list":[128,2]用户被打上的标签ID列表
                                            }	
                                            }*/
                                            wp_update_user(array(
                                                'ID' => $user_id,
                                                'user_nicename' => $user_date->nickname,
                                                'display_name' => $user_date->nickname
                                            ));
                                            update_user_meta($user_id, "sex", ($user_date->sex==1 ? "男" : ($user_date->sex==2 ? "女" : "未知")) );
                                            update_user_meta($user_id, "address", $user_date->country.$user_date->province.$user_date->city );
                                            update_user_meta($user_id, "headimgurl", $user_date->headimgurl);
                                            update_user_meta($user_id, "tagid_list", $user_date->tagid_list);
                                            //如果用户是通过扫描二维码关注，则查询用户的来路并指派给上级
                                            $table_msg      = $wpdb->prefix . 'msg';        //通信存储
                                            $table_msg_meta = $wpdb->prefix . 'msg_meta';   //通信存储meta
                                            $follower_from  = $wpdb->get_var("select key3.msg_value 
                                                from {$table_msg} key1 
                                                inner join {$table_msg_meta} key2 
                                                      on key2.msg_id = key1.msg_id and key2.msg_key = 'Event' and key2.msg_value = 'subscribe' 
                                                inner join {$table_msg_meta} key3 
                                                      on key3.msg_id = key1.msg_id and key3.msg_key = 'EventKey' 
                                                where key1.msg_from_user = '{$openid}' order by key1.msg_time asc");
                                            if($follower_from){
                                                $number = preg_replace("/\D/", "", $follower_from);
                                                //上级用户数据
                                                $customer_info = bls_weixin_user_meta_by_number($number);
                                                //上级用户如果有销售经理则计入上级用户的上级，否则计入当前上级用户
                                                $saleman = isset($customer_info->saleman) && $customer_info->saleman>0 ? $customer_info->saleman : $customer_info->ID;
                                                update_user_meta($user_id, "saleman", $saleman );
                                                //如果是扫描客户二维码的话
                                                if($customer_info->number>20000){
                                                    $numbers = get_bls_weixin_option("sd",false);
                                                    $redirect_url = add_query_arg(array("u_id"=>$user_id,"from"=>$customer_info->number),user_url);
                                                    bls_weixin_work_msg(array(
                                                        "numbers" => $numbers,
                                                        "url"     => add_query_arg("redirect_url",urlencode($redirect_url),login_url),
                                                        "title"   => "请甄别【{$user_date->nickname}】是老客户吗？",
                                                        "work"    => "如果是，请继续点击查看，并做处理，不是请忽略！",
                                                    ));
                                                } else {
                                                    //鼓励一下咱们的员工
                                                    $saleman_info = bls_weixin_user_meta($saleman);
                                                    $salemans = $wpdb->get_col("select distinct user_id from {$wpdb->usermeta} where meta_key='saleman' and meta_value='{$saleman_info->ID}'");
                                                    $followers = $wpdb->get_col("select distinct key1.msg_from_user
                                                        from {$table_msg} key1
                                                        inner join {$table_msg_meta} key2
                                                              on key2.msg_id = key1.msg_id and key2.msg_key = 'Event' and key2.msg_value = 'subscribe'
                                                        inner join {$table_msg_meta} key3
                                                              on key3.msg_id = key1.msg_id and key3.msg_key = 'EventKey' and key3.msg_value = 'qrscene_{$saleman_info->number}'
                                                        where key1.msg_from_user != '' order by key1.msg_time asc");
                                                    $nickname = $saleman_info->truename != "" ? $saleman_info->truename : $saleman_info->display_name;
                                                    bls_weixin_work_msg(array(
                                                        "numbers" => $number,
                                                        "url"     => add_query_arg("u_id", $user_id, user_url),
                                                        "title"   => "恭喜你已经是客户【{$nickname}】的专席客户/业务经理！记得多多互动哦！",
                                                        "work"    => "你已经成为【".count($salemans)."】位用户的专席客户/业务经理，还有【".count($followers)."】个通过扫描你的二维码关注公众号的粉丝！继续加油~~",
                                                    ));
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $user_number= get_new_uid();
                                    $user_login = "2-".$user_number;
                                    $user_id    = wp_insert_user(array(
                                        'user_login'    => $user_login,
                                        'user_pass'     => NULL,
                                        'role'          => 'bls_customer'
                                    ));
                                }
                                if($user_id>0){
                                    //写入用户基本数据
                                    update_user_meta($user_id, "show_admin_bar_front", "false");
                                    update_user_meta($user_id, "openid", $openid);                                    
                                    //写入初始积分
                                    $point_default = get_bls_weixin_option("point_default",0);
                                    bls_weixin_points($user_id, $point_default, __("用户初始的积分值",'bls-weixin'));
                                    //告诉客户服务部有新人需要甄别并发送提醒消息
                                    $user_info = bls_weixin_user_meta($user_id);
                                    $nickname = $user_info->truename != "" ? $user_info->truename : $user_info->display_name;
                                    //给人事行政部发送提醒消息
                                    $numbers = get_bls_weixin_option("hr",false);
                                    bls_weixin_work_msg(array(
                                        "numbers" => $numbers,
                                        "url"     => add_query_arg("u_id", $user_id,user_url),
                                        "title"   => "请甄别新用户:【{$nickname}】，是公司员工吗？",
                                        "work"    => "如果是，请继续点击查看，并做处理，不是请忽略！",
                                    ));
                                    //发送VIP提醒消息给当前用户
                                    bls_weixin_vip_msg(array(
                                        "openids" => $openid,
                                        "url"     => user_url,
                                        "title"   => "尊贵的VIP:{$nickname}",
                                        "msg"     => "您已经成为我们的终身会员，可以享受全部尊贵服务。",
                                    ));
                                    return true;
                                }
                            }
                        }
                    }
                } else {
                    wp_redirect("https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_base &state=".$timestamp."#wechat_redirect");
                    exit;
                }
            }
        }
    }
    return false;
}
endif;

/**
 * 响应微信回调
 * 接收保存微信订单的反馈数据
**/
add_action('bls_weixin_post_xml', 'bls_weixin_pay_notify');
function bls_weixin_pay_notify($postObj) {
    //微信支付回调处理
    if(isset($postObj->return_code)){
        global $wpdb;
		if ($postObj->return_code == "FAIL") {
			//通信出错
		}
		elseif($postObj->result_code == "FAIL"){
			//业务出错
		}
		else{
			//支付成功 写入数据及推送微信消息
            $table_msg_meta = $wpdb->prefix . 'msg_meta';
            $order_no = $postObj->out_trade_no;
            //如果订单已经纪录则不操作
            $is_order = $wpdb->get_var("select msg_id from {$table_msg_meta} where msg_key='out_trade_no' and msg_value='{$order_no}' ");
            if($is_order && $is_order>0){
            } else {
                //纪录本次全部信息
                $postObj->ToUserName = get_bls_weixin_option("wxid");
                $postObj->FromUserName = $postObj->openid;
                $postObj->MsgType = "wxpay";
                $postObj->CreateTime = current_time("timestamp");
                bls_weixin_save_msg_fn($postObj);
                //给客户服务部发送提醒消息收到了多少钱
                $openid = (string)$postObj->FromUserName;
                $user_info = bls_weixin_user_meta_by_openid($openid);
                $nickname = $user_info->truename != "" ? $user_info->truename : $user_info->display_name;
                $numbers = get_bls_weixin_option("sd",false);
                bls_weixin_work_msg(array(
                    "numbers" => $numbers,
                    "title"   => "刚刚微信上收到".$postObj->fee_type.($postObj->cash_fee*0.01)."元",
                    "url"     => add_query_arg("u_id", $user_info->ID, user_url),
                    "work"    => "请及时确认客户信息",
                ));
                //如果有客户经理，鼓励一下
                if(isset($user_info->saleman) && intval($user_info->saleman)>0){
                    $saleman = $user_info->saleman;
                    bls_weixin_work_msg(array(
                        "numbers" => $numbers,
                        "title"   => "刚刚微信上收到".$postObj->fee_type.($postObj->cash_fee*0.01)."元",
                        "url"     => add_query_arg("u_id", $user_info->ID, user_url),
                        "work"    => "请及时确认客户信息",
                    ));
                }
            }
        }
        echo arrayToXml(array("return_code"=>"SUCCESS","return_msg"=>"OK"));
        exit;
    }
}




/*
 * 判断是否是微信浏览器
 * 是则输出true 否则输出false
**/
if(!function_exists('is_weixin')):
function is_weixin(){
    $str = $_SERVER['HTTP_USER_AGENT']; 
	if ( stripos($str, 'MicroMessenger') !== false ) {
        $str = substr($str,stripos($str, 'MicroMessenger'),strlen($str));
        $arr = preg_split("/\s/",$str);
        foreach($arr as $key=>$val){
            $arr[$key] = preg_replace("/^(.+)\//","",$val);
        }
        $arr[$key+1] = $str;
        return $arr;
	}	
	return false;
}
endif;