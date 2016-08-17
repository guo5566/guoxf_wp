<?php  
/*
Plugin Name: yxcheckcode plugin
Plugin URI: http://var.bstarx.com/
Description: 营销验证码插件
Version: 1.0.0
Author: guoxf
Author URI: http://www.bstarx.com/
License: GPL
*/


// if(is_admin()) {
    /*  利用 admin_menu 钩子，添加菜单 */
   // add_action('admin_menu', 'display_gycheckcode_menu');
//} 

// function display_gycheckcode_menu() {
    /* add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function);  */
    /* 页名称，菜单名称，访问级别，菜单别名，点击该菜单时的回调函数（用以显示设置页面） */
    //add_menu_page($page_title, $menu_title, $capability, $menu_slug)
   
   // add_menu_page('设置营销验证码', '设置营销验证码', 'manage_options','qrcheckcode', 'display_checkcode_html_page');
    
    //	add_menu_page( '券票', '券票', 'manage_options', 'bls_vr_tickets', 'bls_vr_tickets_fn', 'dashicons-tickets');
    
    
//} 


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if(!function_exists('display_checkcode_html_page')):
function display_checkcode_html_page() {
    $object_code_img="123456";
    $checkcode=$_GET["checkcode"];
    if(null==$checkcode||""==$checkcode){
        echo "验证码为空";
        
        return;
        
    }
    
    
   // $object_code="http://127.0.0.1/guoxf_wp/wp-admin/admin.php?page=qrcheckcode&checkcode=".$checkcode;
   //http://vr.bstarx.com/wp-admin/
    $object_code="http://vr.bstarx.com/wp-admin/admin.php?page=qrcheckcode&checkcode=".$checkcode;
    $object_code_img=$checkcode;
    require_once(bls_vr_dir."core/qrcode/qrlib.php");
    QRcode::png($object_code, bls_vr_dir.'core/qrcode/'.$object_code_img.'.png', QR_ECLEVEL_L, 5);
    
    global $wpdb;
    //$ajax_none = wp_create_nonce( "bls_vr_nonce" );
    $post = $wpdb->get_row("select * from {$wpdb->posts} where post_type='ticket' and post_title='{$checkcode}'");
    //echo "123456 --------".$post->menu_order;
    if($post && isset($post->menu_order) && $post->menu_order!=0){
        global $current_user;
        $timestamp          = current_time("timestamp");
        $post_title         = $post->post_title;
        $menu_order         = max(0, $post->menu_order - 1);
        $post_excerpt       = $post->post_excerpt ? maybe_unserialize($post->post_excerpt) : array();
        $post_excerpt       = is_array($post_excerpt) ? $post_excerpt : array();
        $post_excerpt[] = array(
            "user_id"   => $current_user->ID,
            "used_time" => $timestamp,
            "object"    => "核销了1次"
        );
        $post = array(
            'ID'            => $post->ID,
            'post_excerpt'  => maybe_serialize($post_excerpt),
            'menu_order'    => $menu_order
        );
      $pid= wp_update_post( $post );
       //echo "~~~~~".$menu_order."==================".$pid;
        //更改项目消费状态
        if($menu_order==0){
            $object = get_bls_object($post_title);
            if($object){
                update_bls_object($object["object_id"], array("object_status"=>1));
            }
        }
    } 
    $post = $wpdb->get_row("select * from {$wpdb->posts} where post_type='ticket' and post_title='{$checkcode}'");
    //echo "<br/>============".$post->menu_order;
       if($post->menu_order==0){
           $html = "已经全部核销";
       }else{
           $html = "核销成功1次";
       }       
    ?>
    <div>  
        <h2>验证码核销</h2>
         <div class="wrap">
        <img width="200" height="200" src="<?php echo bls_vr_url.'core/qrcode/'.$object_code_img.'.png'; ?>" alt="">
        </div>
        <div class="wrap">
        验证码：<?php echo $checkcode;?>
        </div>
        <div class="wrap">
         <p class="submit">
            <input type="button" disabled name="submit" class="button large-button" value="<?php echo $html; ?>">
        </p>
        </div>
         
        
    </div>  
<?php  
}

endif;

?>




