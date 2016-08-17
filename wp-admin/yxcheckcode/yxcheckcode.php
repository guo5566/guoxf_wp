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


if(is_admin()) {
    /*  利用 admin_menu 钩子，添加菜单 */
    add_action('admin_menu', 'display_gycheckcode_menu');
}

function display_gycheckcode_menu() {
    /* add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function);  */
    /* 页名称，菜单名称，访问级别，菜单别名，点击该菜单时的回调函数（用以显示设置页面） */
    //add_menu_page($page_title, $menu_title, $capability, $menu_slug)
   
    add_menu_page('设置营销验证码', '设置营销验证码', 'administrator','设置营销验证码', 'display_checkcode_html_page');
    
    
    
}


function display_checkcode_html_page() {
    ?>
    <div>  
        <h2>设置营销验证码</h2>
        
        <table border="0"  style="width: 100%;  border:solid 1px" >
          <tr>
           <td colspan="4" align="right" style="padding-right: 30px">
           <a href="./addcode.php">添加</a>
           </td>
           </tr>
          <tr> 
           <td width="10%">ID</td>
           <td width="20%">用户ID</td>
           <td width="30%">验证码</td>
           <td width="50%">操作</td>
          </tr>
        
        </table>  
        
    </div>  
<?php  
}



?>




