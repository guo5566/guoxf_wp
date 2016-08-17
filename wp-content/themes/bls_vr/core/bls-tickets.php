<?php
/**
 * 票券 * 核心
 * ver 1.0 core
**/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 采用post数据库存储票券
 * post_type=ticket 票据专用
 * post_status=票据类型 1代金券 2打折 3兑换 4预约码 5推荐码
 * post_title=券码
 * post_content=array(
 *    money=>减多少钱
 *    discount=>打几折
 *    exchange=>兑换的套餐post_id
 *    reservation=>用户user_id
 *    branding=>用户user_id
 * )
 * post_excerpt=array(
 *    array(user_id=>操作者,used_time=>操作时间戳,object=>操作/项目详情)
 *    ...
 * )
 * post_password=过期时间戳
 * menu_order=最多使用次数
**/
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class bls_vr_tickets_list_table extends WP_List_Table {
    /*
     * 初始化
    **/
    function __construct(){
        global $status, $page;
                
        //设置父级类的参数
        parent::__construct( array(
            'singular'  => 'ticket',  //数据集的标识
            'plural'    => 'tickets', //标识复数
            'ajax'      => true       //是否支持ajax?
        ) );
        
    }
    
    /**
     * 序列化表格项目列，如果保留 'cb' 项目，请先创建 column_cb()函数.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
    **/
    function get_columns(){
        $columns = isset($_GET['action']) && $_GET['action']=="edit" ? array(
            'user_id'       => '操作用户',
            'used_time'     => '操作时间',
            'object'        => '操作详情'
        ) : array(
            'post_title'    => '券码',
            'post_status'   => '类型',
            'post_content'  => '描述',
            'post_date'     => '创建时间',
            'post_password' => '过期时间',
            'menu_order'    => '最多使用次数'
        );
        return $columns;
    }


    /**
     * 允许按以下表格项目升、降序显示
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
    **/
    function get_sortable_columns() {
        $sortable_columns = array(
            'post_title'    => array('post_title',false),
            'post_status'   => array('post_status',false),
            'post_date'     => array('post_date',false),
            'post_password' => array('post_password',false),
            'menu_order'    => array('menu_order',false)
        );
        return $sortable_columns;
    }

    /*
     * 默认表格项目 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item (本行项目数据)
     * @param array $column_name The name/slug of the column to be processed
     * @return 会在表格项目的<td>标签中置入字符
    **/
    function column_default($item, $column_name){
        if(isset($item[$column_name])){
            $str = $item[$column_name];
            $html = $str;
            $ticket = isset($item['ID']) ? intval($item['ID']) : "";
            global $wpdb;
            switch($column_name){
                case "post_title":
                    $actions = array(
                        'delete'=> sprintf('<a href="?page=%s&%saction=%s&ticket=%s">%s</a>',$_REQUEST['page'],(isset($_GET["paged"]) ? "paged=".$_GET["paged"]."&" : ""),'delete',$ticket,"删除"),
                        'edit'=> sprintf('<a href="?page=%s&%saction=%s&ticket=%s">%s</a>',$_REQUEST['page'],(isset($_GET["paged"]) ? "paged=".$_GET["paged"]."&" : ""),'edit',$ticket,"查看编辑")
                    );
                    $html = $str.$this->row_actions($actions);
                break;
                case "post_status":
                    $html = "<code>";
                    switch($str){
                        case "1":
                            $html .= "代金券";
                        break;
                        case "2":
                            $html .= "打折券";
                        break;
                        case "3":
                            $html .= "兑换券";
                        break;
                        case "4":
                            $html .= "预约码";
                        break;
                        case "5":
                            $html .= "推荐码";
                        break;
                        default:
                            $html .= "其他券";
                        break;
                    }
                    $html .= "</code>";
                break;
                case "post_content":
                    $str = maybe_unserialize($str);
                    switch( $str["rule"]){
                        case 1:
                            $html = " 可以减【".$str["money"]."】元钱";
                        break;
                        case 2:
                            $html = "可以打【".$str["discount"]."】折";
                        break;
                        case 3:
                            $html = "兑换套餐";
                            $post = get_post(intval($str["exchange"]));
                            if($post){
                                $html .= "【".$post->post_title."】";
                            }
                        break;
                        case 4:
                            $user_info = get_userdata($str["reservation"]);
                            $nickname  = isset($user_info->display_name) ? " 用户【".$user_info->display_name."】专用" : "--";
                            $html = $nickname ;
                        break;
                        case 5:
                            $user_info = get_userdata($str["branding"]);
                            $nickname  = isset($user_info->display_name) ? " 用户【".$user_info->display_name."】专用" : "--";
                            $html = $nickname ;
                        break;
                    }
                break;
                case "post_password":
                    $html = $str ? date("Y-m-d H:i:s", $str) : "--";
                break;
                case "menu_order":
                    $html = $str>-1 ? $str : "--";
                break;
            }
            return $html;
        } else {
            $html = $column_name." not in Array(";
            foreach($item as $key=>$val){
                $html .= "{$key}=>{$val},";
            }
            return $html.")";
        }
    }
                    

    /**
     * 给table list赋值
     * 至少要设置一下翻页条 $this->items 和 $this->set_pagination_args()
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {        
        //本参数必须设置！第一组是全部数据，第二组是隐藏的数据项，第三组是供排序
        $columns        = $this->get_columns();
        $hidden         = array();
        $sortable       = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        //本参数必须设置！$this->items加载数据生成表格
        global $wpdb;
        $total_items    = 1; //总数据数量
        $per_page       = 15; //每个页面显示数量
        $current_page   = $this->get_pagenum(); //当前页码
        //排序及数量
        $order_by       = " order by ".(isset($_GET["orderby"]) ? $_GET["orderby"] : "ID");
        $order_sort     = isset($_GET["order"]) ? $_GET["order"] : "desc";
        $order_limit    = " limit ".($per_page * ($current_page - 1)).",".$per_page;
        //加载数据
        if(isset($_GET['action']) && $_GET['action']=="edit" && $_GET['ticket']){
            $sortable   = array();
            $ticket     = $_GET['ticket'];
            $items      = $wpdb->get_var("select post_excerpt from {$wpdb->posts} where post_type='ticket' and (ID='{$ticket}' or post_title='{$ticket}')");
            $total_items = 0;
            $this->items = array();
            if($items){
                $items = maybe_unserialize($items);
                foreach($items as $key=>$val){
                    $total_items++;
                    $user_info = get_userdata($val["user_id"]);
                    if(is_numeric($val["object"])){
                        
                    } else {
                        $object = $val["object"];
                    }
                    $this->items[] = array(
                        'user_id'       => $user_info->display_name,
                        'used_time'     => date("Y-m-d H:i:s",$val["used_time"]),
                        'object'        => $object
                    );
                }
            }
        } else {
            $total_items = $wpdb->get_var("select count(*) from {$wpdb->posts} where post_type='ticket' ");
            $this->items = $wpdb->get_results("select * from {$wpdb->posts} where post_type='ticket' {$order_by} {$order_sort} {$order_limit}", ARRAY_A);
        }
        
        //本参数必须设置！供生成翻页条和计算页面
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //总数据量
            'per_page'    => $per_page,                     //每个页面显示数量
            'total_pages' => ceil($total_items/$per_page)   //总页码数
        ) );
    }

}

/**
 * 券票管理
**/
if(!function_exists('bls_vr_tickets_fn')):
function bls_vr_tickets_fn(){
    global $wpdb;
    $ajax_none = wp_create_nonce( "bls_vr_nonce" );
    if(isset($_GET['action'])){
        switch($_GET['action']){
            case "add":                
                //增加票券
                if(isset($_POST["_wpnonce"]) && wp_verify_nonce($_POST["_wpnonce"], "bls_vr_nonce")){
                    $post_title = isset($_POST["post_title"]) ? sanitize_text_field($_POST["post_title"]) : "";
                    $post_content = isset($_POST["post_content"]) ? $_POST["post_content"] : array("rule"=>4);
                    $post_password = isset($_POST["post_password"]) ? strtotime($_POST["post_password"]) : "";
                    $menu_order = isset($_POST["menu_order"]) ? intval($_POST["menu_order"]) : 1;
                    if($post_title && $post_content){
                        $post = array(
                            'post_type'     => "ticket",
                            'post_status'   => $post_content["rule"],
                            'post_title'    => $post_title,
                            'post_content'  => maybe_serialize($post_content),
                            'post_password' => $post_password,
                            'menu_order' => $menu_order
                        );
                        $post_id = wp_insert_post( $post );
                    }
                    wp_safe_redirect(admin_url("admin.php?page=".$_REQUEST['page'])."&action_result=true");
                    exit;
                }
                
                
                
?>
<style>
input[type=radio] + span input{
    opacity:0;
}
input[type=radio]:checked + span input{
    opacity:1;
}
</style>
<div class="wrap options-general-php">
    <h2>增加一张券票</h2>
    <form method="post">
        <input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo $ajax_none; ?>">
        <table class="form-table">
            <tr>
                <th scope="row"> <label for="post_title">券票号码</label>
                </th>
                <td><input readonly name="post_title" type="text" id="post_title" value="<?php unique_ticket(); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th scope="row"> <label for="type">使用规则</label>
                </th>
                <td>
                    <fieldset>
                        <label>
                            <input type="radio" name="post_content[rule]" value="1" checked="checked">
                            <span class="date-time-text format-i18n">代金券 <input type="text" name="post_content[money]" value="0" class="small-text"></span><code>减多少钱（不小于0）</code></label>
                        <br>
                        <label>
                            <input type="radio" name="post_content[rule]" value="2">
                            <span class="date-time-text format-i18n">打折券 <input type="text" name="post_content[discount]" value="10" class="small-text"></span><code>打几折（不大于10）</code></label>
                        <br>
                        <label>
                            <input type="radio" name="post_content[rule]" value="3">
                            <span class="date-time-text format-i18n">兑换券 <input type="text" name="post_content[exchange]" value="" class="small-text"></span><code>兑换的套餐（套餐id用,分隔）</code></label>
                        <br>
                        <label>
                            <input type="radio" name="post_content[rule]" value="4">
                            <span class="date-time-text format-i18n">预约码 <input type="text" name="post_content[reservation]" value="" class="small-text"></span><code>用户预约来店消费使用</code></label>
                        <br>
                        <label>
                            <input type="radio" name="post_content[rule]" value="5">
                            <span class="date-time-text format-i18n">推荐码 <input type="text" name="post_content[branding]" value="" class="small-text"></span><code>用户发展下线识别码</code></label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row"> <label for="unit">过期时间</label>
                </th>
                <td><input name="post_password" type="datetime-local" id="post_password" value="" class="regular-text" /><p class="description">如果不设定过期间请留空。</p></td>
            </tr>
            <tr>
                <th scope="row"> <label for="unit">使用次数</label>
                </th>
                <td><input name="menu_order" type="number" step="1" id="menu_order" value="1" class="regular-text" /><p class="description">如果不限定使用次数请填写-1。</p></td>
            </tr>
        </table>
        <?php submit_button("确定"); ?>
    </form>
</div>
<?php
            break;
            case "delete":
                if(isset($_GET["ticket"])){
                    $ticket = intval($_GET["ticket"]);
                    wp_delete_post($ticket);
                }
                wp_safe_redirect(admin_url("admin.php?page=".$_REQUEST['page']).(isset($_GET['paged']) ? '&paged='.$_GET['paged'] : "")."&action_result=true");
                exit;
            break;
            case "edit":
                if(isset($_POST["_wpnonce"]) && wp_verify_nonce($_POST["_wpnonce"], "bls_vr_nonce")){
                    $post_id = isset($_POST["post_id"]) ? intval($_POST["post_id"]) : "";
                    $post_content = isset($_POST["post_content"]) ? $_POST["post_content"] : array("rule"=>4);
                    $post = get_post($post_id);
                    if($post && $post_content){
                        global $current_user;
                        $timestamp          = current_time("timestamp");
                        $post_password      = isset($_POST["post_password"]) ? strtotime($_POST["post_password"]) : "";
                        $menu_order         = isset($_POST["menu_order"]) ? intval($_POST["menu_order"]) : 1;
                        $post_excerpt       = $post->post_excerpt ? maybe_unserialize($post->post_excerpt) : array();
                        $post_excerpt       = is_array($post_excerpt) ? $post_excerpt : array();
                        $post_excerpt[] = array(
                            "user_id"   => $current_user->ID,
                            "used_time" => $timestamp,
                            "object"    => "修改了券票"
                        );
                        $post = array(
                            'ID'            => $post_id,
                            'post_type'     => "ticket",
                            'post_excerpt'  => maybe_serialize($post_excerpt),
                            'post_status'   => $post_content["rule"],
                            'post_content'  => maybe_serialize($post_content),
                            'post_password' => $post_password,
                            'menu_order'    => $menu_order
                        );
                        $post_id = wp_update_post( $post );
                        //根据修改更换
                        if($post_content["rule"] == 4){
                            $post = get_post($post_id);
                            $object = get_bls_object($post->post_title);
                            if($object){
                                update_bls_object($object["object_id"], array("object_status" => ($menu_order==0 ? 1 : 0)));
                            }
                        }
                    }
                    wp_safe_redirect(admin_url("admin.php?page=".$_REQUEST['page'])."&action_result=true");
                    exit;
                }
                if(isset($_POST["consume"]) && wp_verify_nonce($_POST["consume"], "bls_vr_nonce")){
                    $post_id = isset($_POST["post_id"]) ? intval($_POST["post_id"]) : "";
                    $post = get_post($post_id);
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
                            'ID'            => $post_id,
                            'post_excerpt'  => maybe_serialize($post_excerpt),
                            'menu_order'    => $menu_order
                        );
                        wp_update_post( $post );
                        //更改项目消费状态
                        if($menu_order==0){
                            $object = get_bls_object($post_title);
                            if($object){
                                update_bls_object($object["object_id"], array("object_status"=>1));
                            }
                        }
                    }
                    wp_safe_redirect(admin_url("admin.php?page=".$_REQUEST['page'])."&action=edit&ticket=".$post_id."&action_result=true");
                    exit;
                }
                //实例化类
                $wp_list_table = new bls_vr_tickets_list_table();
                //运行：可以获取数据、分页条
                $wp_list_table->prepare_items();    
                ?>
<style>
input[type=radio] + span input{
    opacity:0;
}
input[type=radio]:checked + span input{
    opacity:1;
}
.large-button{
    font-size: 24px !important;
    line-height: 80px !important;
    height:80px !important;
    border-radius:50%;
    margin: 0 !important;
    padding: 0 40px !important;
}
</style>
<div class="wrap">
    <h2>券票明细 <a href="?page=bls_vr_tickets&action=add" class="page-title-action">增加券票</a> </h2>
    <form method="get" action="<?php echo admin_url("admin.php?page=".$_REQUEST['page']); ?>">
        <p class="search-box">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <input type="hidden" name="action" value="edit" />
            <input type="search" id="post-search-input" name="ticket" value="">
            <input type="submit" id="search-submit" class="button" value="搜索券票">
        </p>
        <?php $wp_list_table->display() ?>
    </form>
    <?php
    if ( $wp_list_table->has_items() )
        $wp_list_table->inline_edit();
    if($_GET['ticket']){
        $ticket     = $_GET['ticket'];
        $post       = $wpdb->get_row("select * from {$wpdb->posts} where post_type='ticket' and (ID='{$ticket}' or post_title='{$ticket}')");
        if($post){
            $post_content = maybe_unserialize($post->post_content);
            $post_excerpt = maybe_unserialize($post->post_excerpt);
            if($post_content["rule"]==4){
    ?>
    <hr>
    <h2>券票核销</h2>
    <form method="post">
        <input type="hidden" id="consume" name="consume" value="<?php echo $ajax_none; ?>">
        <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>">
        <?php
                if($post->menu_order==0){
                    switch($post->post_status){
                        default:
                        case "used":
                            $html = "已经全部核销";
                        break;
                        case "expired":
                            $html = "过期无法核销";
                        break;
                    }
        ?>
        <p class="submit">
            <input type="button" disabled name="submit" class="button large-button" value="<?php echo $html; ?>">
        </p>
        <?php
            } else {
        ?>
        <p class="submit">
            <input type="submit" name="submit" class="button large-button button-primary" value="确认核销1次">
        </p>
        <?php
            }
        ?>
    </form>
    <?php
        }
    ?>
    <hr>
    <h2>券票修改</h2>
    <?php echo (isset($_GET["action_result"]) ? ($_GET["action_result"] ? '<div id="message" class="updated"><p><strong> '.__('操作成功!','bls-zzz').' </strong></p></div>' : '<div id="message" class="error"><p><strong> '.__('操作失败!','bls-zzz').' </strong></p></div>') : ""); ?>
    <form method="post">
        <input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo $ajax_none; ?>">
        <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>">
        <table class="form-table">
            <tr>
                <th scope="row"> <label for="post_title">券票号码</label>
                </th>
                <td><input readonly name="post_title" type="text" id="post_title" value="<?php echo $post->post_title; ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th scope="row"> <label for="type">使用规则</label>
                </th>
                <td>
                    <fieldset>
                        <label>
                            <input type="radio" name="post_content[rule]" value="1" <?php checked(1,$post_content["rule"]); ?>>
                            <span class="date-time-text format-i18n">代金券 <input type="text" name="post_content[money]" value="<?php echo $post_content["money"]; ?>" class="small-text"></span><code>减多少钱（不小于0）</code></label>
                        <br>
                        <label>
                            <input type="radio" name="post_content[rule]" value="2" <?php checked(2,$post_content["rule"]); ?>>
                            <span class="date-time-text format-i18n">打折券 <input type="text" name="post_content[discount]" value="<?php echo $post_content["discount"]; ?>" class="small-text"></span><code>打几折（不大于10）</code></label>
                        <br>
                        <label>
                            <input type="radio" name="post_content[rule]" value="3" <?php checked(3,$post_content["rule"]); ?>>
                            <span class="date-time-text format-i18n">兑换券 <input type="text" name="post_content[exchange]" value="<?php echo $post_content["exchange"]; ?>" class="small-text"></span><code>兑换的套餐（套餐id用,分隔）</code></label>
                        <br>
                        <label>
                            <input type="radio" name="post_content[rule]" value="4" <?php checked(4,$post_content["rule"]); ?>>
                            <span class="date-time-text format-i18n">预约码 <input type="text" name="post_content[reservation]" value="<?php echo $post_content["reservation"]; ?>" class="small-text"></span><code>用户预约来店消费使用</code></label>
                        <br>
                        <label>
                            <input type="radio" name="post_content[rule]" value="5" <?php checked(5,$post_content["rule"]); ?>>
                            <span class="date-time-text format-i18n">推荐码 <input type="text" name="post_content[branding]" value="<?php echo $post_content["branding"]; ?>" class="small-text"></span><code>用户发展下线识别码</code></label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th scope="row"> <label for="unit">过期时间</label>
                </th>
                <td><input name="post_password" type="datetime-local" id="post_password" value="<?php echo $post->menu_order; ?>" class="regular-text" /><p class="description">如果不设定过期间请留空。</p></td>
            </tr>
            <tr>
                <th scope="row"> <label for="unit">使用次数</label>
                </th>
                <td><input name="menu_order" type="number" step="1" id="menu_order" value="<?php echo $post->menu_order; ?>" class="regular-text" /><p class="description">如果不限定使用次数请填写-1。</p></td>
            </tr>
        </table>
        <?php submit_button("确定"); ?>
        <?php
        }
        ?>
    </form>
    <?php
    }
    ?>
</div>
<?php
            break;
        }
    } else {
        //实例化类
        $wp_list_table = new bls_vr_tickets_list_table();
        //运行：可以获取数据、分页条
        $wp_list_table->prepare_items();    
    ?>
<div class="wrap">
    <h2>券票管理 <a href="?page=bls_vr_tickets&action=add" class="page-title-action">增加券票</a> </h2>
    <?php echo (isset($_GET["action_result"]) ? ($_GET["action_result"] ? '<div id="message" class="updated"><p><strong> '.__('操作成功!','bls-zzz').' </strong></p></div>' : '<div id="message" class="error"><p><strong> '.__('操作失败!','bls-zzz').' </strong></p></div>') : ""); ?>
    <form method="get" action="<?php echo admin_url("admin.php?page=".$_REQUEST['page']); ?>">
        <p class="search-box">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <input type="hidden" name="action" value="edit" />
            <input type="search" id="post-search-input" name="ticket" value="">
            <input type="submit" id="search-submit" class="button" value="搜索券票">
        </p>
        <?php $wp_list_table->display() ?>
    </form>
    <?php
        if ( $wp_list_table->has_items() ) $wp_list_table->inline_edit();
    ?>
</div>
<?php
    }
}
endif;

/**
 * Change code lost you hands
**/
