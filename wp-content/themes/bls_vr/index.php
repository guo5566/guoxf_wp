<?php
/**
 * @package WordPress Template Name: 首页界面 v1.0
**/
global $wp_query,$current_user,$post,$wpdb;
//响应Ajax请求
if(isset($_GET["action"])):
    $timestamp = current_time("timestamp");
    switch($_GET["action"]){
        case "wxpay_notify":
            $order_no = isset($_POST["order_no"]) ? sanitize_text_field($_POST["order_no"]) : false;
            $sku = isset($_POST["sku"]) ? intval($_POST["sku"]) : 0;
            //必须登录
            if($order_no && $sku>0 && $current_user->ID>0){
                //查询付款用户的openid
                $table_msg      = $wpdb->prefix . 'msg';
                $table_msg_meta = $wpdb->prefix . 'msg_meta';
                $table_object   = $wpdb->prefix . 'object';
                //付款成功则会保存数据和写入订单号
                $msg_id         = $wpdb->get_var("select key1.msg_id 
                    from {$table_msg_meta} key1 
                    inner join {$table_msg} key2 
                          on key2.msg_id = key1.msg_id
                    where key1.msg_key = 'out_trade_no' and key1.msg_value = '{$order_no}'
                ");
                if($msg_id){
                    //是否写入订单号
                    $object_code    = $wpdb->get_var("select object_code from {$table_object} where object_order_no = '{$order_no}' ");
                    if($object_code){
                        //已经付过款了
                    } else {
                        $user_id = $current_user->ID;
                        //派发卡券一张
                        $object_code = get_unique_ticket();
                        $rule = 4; //4是预约码
                        $post_content = array(
                            "rule"=>$rule,
                            "money"=>0,
                            "discount"=>10,
                            "exchange"=>"",
                            "reservation"=>$user_id,
                            "branding"=>""
                        );
                        $post = array(
                            'post_type'     => "ticket",
                            'post_status'   => $rule,
                            'post_title'    => $object_code,
                            'post_content'  => maybe_serialize($post_content),
                            'post_password' => "",
                            'menu_order'    => 1
                        );
                        $post_id = wp_insert_post( $post ); 
                        //写入商品信息
                        $cost = get_post_meta($sku, "cost", true);
                        $cash_fee = $wpdb->get_var("select msg_value from {$table_msg_meta} where msg_id={$msg_id} and msg_key='cash_fee'");
                        $arg = array(
                            'object_order_no'   => $order_no, //项目内部唯一编号                          
                            'object_goods'      => $sku, //购买套餐 post_id
                            'object_price'      => $cost, //支付总金额            
                            'object_cost'       => $cash_fee*0.01, //实际支付金额
                            'object_code'       => $object_code, //预约码 post_title
                            'object_status'     => 0 //状态 0待用 1已经使用
                        );
                        if(isset($_POST["ticket"])){
                            $ticket = $wpdb->get_row("select * from $wpdb->posts where post_title='".sanitize_text_field($_POST["ticket"])."' and post_type='ticket'");
                            if($ticket){
                                if($ticket->menu_order!=0 && ($ticket->post_password=="" || $ticket->post_password>$timestamp )){
                                    $post_content = maybe_unserialize($ticket->post_content);
                                    switch($post_content["rule"]){
                                        case 1:
                                            $arg['object_ticket']   = $_POST["ticket"]; //使用的票券 post_title
                                            $arg['object_note'] = "抵扣".(min($post_content["money"], $cost - $cash_fee*0.01))."元";
                                        break;
                                        case 2:
                                            $arg['object_ticket']   = $_POST["ticket"]; //使用的票券 post_title
                                            $arg['object_note'] = "打".$post_content["discount"]."折";
                                        break;
                                    }
                                }
                                if($ticket->menu_order>0){
                                    //修改使用次数
                                    $menu_order         = max(0, $ticket->menu_order-1);
                                    $post_excerpt       = maybe_unserialize($ticket->post_excerpt);
                                    $post_excerpt       = is_array($post_excerpt) ? $post_excerpt : array();
                                    $post_excerpt[] = array(
                                        "user_id"   => $current_user->ID,
                                        "used_time" => $timestamp,
                                        "object"    => "使用了券票"
                                    );
                                    $post = array(
                                        'ID'            => $ticket->ID,
                                        'post_excerpt'  => maybe_serialize($post_excerpt),
                                        'menu_order'    => $menu_order
                                    );
                                    $post_id = wp_update_post( $post );
                                    //更改项目消费状态
                                    if($menu_order==0){
                                        $object = get_bls_object(sanitize_text_field($_POST["ticket"]));
                                        if($object){
                                            update_bls_object($object["object_id"], array("object_status"=>1));
                                        }
                                    }
                                }
                            }
                        }
                        add_bls_object($user_id,$arg);
                        //用户接收卡券
                        $user_info = bls_weixin_user_meta($user_id);
                        $tickets = $user_info->tickets;//优惠券
                        if(!isset($tickets[$object_code])){
                            //array(id1=>type1, 卡券号=>类型
                            $tickets[$object_code] = 4;
                            update_user_meta($user_id, "tickets", $tickets);
                        }
                    }                    
                    include_once(bls_vr_dir."core/qrcode/qrlib.php");    
                    $qrcode = $object_code;
                    QRcode::png($qrcode, bls_vr_dir.'core/qrcode/'.$object_code.'.png', QR_ECLEVEL_L, 5);
?>
<?php
?>
    <div class="tabbar">
        <form method="get" class="weui_tab" action="<?php echo user_url; ?>">
            <div class="weui_tab_bd">
                <div class="weui_msg bu">
                    <div class="weui_icon_area">
                        <h2 class="weui_msg_title"><em>恭喜您，预约成功</em></h2>
                        <img width="200" height="200" src="<?php echo bls_vr_url.'core/qrcode/'.$object_code.'.png'; ?>" alt="">
                        <p class="weui_msg_desc">预约码： <em><?php echo $object_code; ?></em></p>
                    </div>
                    <div class="weui_text_area">
                        <ol>使用提示：
                        <li>建议收藏本页面</li>
                        <li>请向工作人员出示预约码，或者现场用微信扫二维码来确认订单</li>
                        <li>场馆内不收现金，一切以微信支付结算</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="weui_tabbar">
                <button type="submit" class="weui_tabbar_item weui_btn weui_btn_primary weui_bar_item_on" id="checkout-btn">查看我的订单</button>
            </div>
        </form>
    </div>
<?php
                }
            } else {
                echo "";
            }
        break;
    }
    exit;
else:
get_header();
//我们把页面内容全部放在sidebar模板中
if( is_home() || is_front_page() || is_archive()):
    // 首页
    get_sidebar( 'home' );
elseif( is_tax("album") ):
    //套餐列表
    get_sidebar( 'album' );
elseif( is_singular() ):
    if(is_page( 'checkout' ) || is_page("user")){
        // 如果没有登录，先去登录
        if(isset($current_user->ID) && $current_user->ID>0){
            if(is_page( 'checkout' ) && isset($_GET["sku"])){
                //有需求才结账
                get_sidebar( 'checkout' );
            } else {
                $tickets = get_user_meta($current_user->ID, "tickets", true);
                if(is_array($tickets) && count($tickets)>0){
                    get_sidebar( 'user' );
                } else {
                    get_sidebar( "none" );
                }
            }
        } else {
            //常规登录
            $protocol   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url        = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            wp_redirect(wp_login_url($url));
            exit;
        }
    } elseif(is_page("genre")){
        // 核心功能：游戏中心
        get_sidebar( 'genre' );
    } elseif(is_page("mall")){
        // 核心功能：套餐选购
        get_sidebar( 'mall' );
    } else {
        // 相当于is_single()||is_page()||is_attachment()
        get_sidebar( $post->post_type );
    }
else:
    // 是否为404页面is_404()
    get_sidebar( "none" );
endif;
get_footer();
do_action("bls_weixin_foot");
endif;
//Last modification time: 2016.7.6
