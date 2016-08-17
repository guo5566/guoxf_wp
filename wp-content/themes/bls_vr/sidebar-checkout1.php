<?php
global $post,$current_user,$wpdb;
$sku = intval($_GET["sku"]);
$goods = get_post($sku);
if($goods->post_type=="goods"){
    $goods_id = $goods->ID;
    $timestamp = current_time("timestamp");
    //当前用户信息
    $user_id    = $current_user->ID;
    $user_info  = bls_weixin_user_meta($user_id);
    $openid     = $user_info->openid;//用户openid
    $order_no   = get_order_no();//统一订单号
    define("order_no", $order_no);
    $cost       = round(get_post_meta($goods_id, "cost", true),2);//应该支付费用
    $goods_body = $goods->post_title;
    //如果有使用券的话
    $str        = "";
    $desc       = "";
    if(isset($_POST["ticket"])){
        $ticket = $wpdb->get_row("select * from $wpdb->posts where post_title='".sanitize_text_field($_POST["ticket"])."' and post_type='ticket'");
        if($ticket){          
            /*
             * post_status=票据状态 valid有效 used已经使用 expired已过期
             * post_title=券码
             * post_content=array(
             *    rule=>使用规则 1代金券 2打折 3兑换 4预约码 5推荐码
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
             */
            if($ticket->menu_order!=0 && ($ticket->post_password=="" || $ticket->post_password>$timestamp )){
                $post_content = maybe_unserialize($ticket->post_content);
                switch($post_content["rule"]){
                    case 1:
                      $cost = max(0.01,round($cost-$post_content["money"],2));
                      $str  = -$post_content["money"]."元";
                    break;
                    case 2:
                      $cost = round($cost*$post_content["discount"]*0.1,2);
                      $str  = $post_content["discount"]."折";
                    break;
                    case 3:
                        if($post_content["exchange"] == $sku && $ticket->menu_order!=0){
                            //直接写入商品并跳转
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
                            //写入购物信息
                            $arg = array(
                                'object_order_no'   => $order_no, //项目内部唯一编号                          
                                'object_goods'      => $sku, //购买套餐 post_id
                                'object_price'      => $cost, //支付总金额            
                                'object_cost'       => 0, //实际支付金额
                                'object_code'       => $object_code, //预约码 post_title
                                'object_status'     => 0 //状态 0待用 1已经使用
                            );
                            add_bls_object($user_id,$arg);
                            //修改使用次数
                            $menu_order         = max(0, $ticket->menu_order-1);
                            $post_excerpt       = maybe_unserialize($ticket->post_excerpt);
                            $post_excerpt       = is_array($post_excerpt) ? $post_excerpt : array();
                            $post_excerpt[] = array(
                                "user_id"   => $user_id,
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
                            //用户接收卡券
                            $user_info = bls_weixin_user_meta($user_id);
                            $tickets = $user_info->tickets;//优惠券
                            if(!isset($tickets[$object_code])){
                                //array(id1=>type1, 卡券号=>类型
                                $tickets[$object_code] = 4;
                                update_user_meta($user_id, "tickets", $tickets);
                            }
                            //回到用户界面
                            wp_redirect(user_url);
                            exit;
                        }
                    break;
                    default:
                      $desc = "优惠码错误";
                    break;
                }
            } else {
                switch($ticket->post_status){
                    case "used":
                        $desc = "优惠码已使用";
                    break;
                    case "expired":
                        $desc = "优惠码已过期";
                    break;
                    default:
                        $desc = "优惠码错误";
                    break;
                }
            }
        } else {
            $desc = "优惠码错误";
        }
    }
    $money      = $cost * 100;//微信是以分为单位
    //是否支持h5
    $is_weixin  = is_weixin();
    $trade_type = isset($is_weixin[0]) && version_compare($is_weixin[0], '5.0') >= 0 ? "JSAPI" : "NATIVE";
    $wx_order = array(
        "appid"             => AppID,
        "mch_id"            => Mchid,
        "nonce_str"         => bls_rand_str(32),
        "body"              => $goods->post_title,
        "out_trade_no"      => $order_no,
        "total_fee"         => $money,
        "spbill_create_ip"  => $_SERVER['REMOTE_ADDR'],
        "notify_url"        => home_url("/?action=wxpay_notify"),
        "trade_type"        => $trade_type,
        "product_id"        => $timestamp
    );
    if($trade_type=="JSAPI"){
        $wx_order["openid"] = $openid;
        add_action("wp_footer","flh_footer_jsapi",100);
    } else {
        add_action("wp_footer","flh_footer_native",100);
    }
    $wx_order["sign"] = getSign($wx_order);//签名
    $wx_order_url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
    $remote_arr = wp_remote_post($wx_order_url,array('body' => arrayToXml($wx_order)));
    $wx_order = xmlToArray(wp_remote_retrieve_body($remote_arr));
    switch($trade_type){
        default:
        case "NATIVE":
            if(isset($wx_order["code_url"])){
                include_once(bls_vr_dir."core/qrcode/qrlib.php");    
                $qrcode = $wx_order["code_url"];
                QRcode::png($qrcode, bls_vr_dir.'core/qrcode/'.$openid.'.png', QR_ECLEVEL_L, 5);
            }
            define("wx_qr_url", bls_vr_url.'core/qrcode/'.$openid.'.png');
        break;
        case "JSAPI":
            if(isset($wx_order["prepay_id"]))
            $wx_order = getParameters($wx_order);
            define("wx_order", $wx_order);
        break;
        case "WAP":
            //忽略
        break;
    }

/**
 * 执行各自脚本
**/
function flh_footer_jsapi(){
?>
<!-- //jsApi -->
<script type="text/javascript">            
//调用微信jsapi
function jsApiCall()
{
    WeixinJSBridge.invoke(
        'getBrandWCPayRequest',
        <?php echo wx_order; ?>,
        function(res){
            WeixinJSBridge.log(res.err_msg);
            if(res.err_msg == "get_brand_wcpay_request:ok" ) {
                //成功
                document.getElementsByTagName("body").className = "in";
            }
        }
    );
}
function checkout()
{
    if (typeof WeixinJSBridge == "undefined"){
        if( document.addEventListener ){
            document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
        }else if (document.attachEvent){
            document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
            document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
        }
    }else{
        jsApiCall();
    }
}
jQuery(function($) {
    $("#checkout-btn").click(function(){
        if (typeof WeixinJSBridge == "undefined"){
            if( document.addEventListener ){
                document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
            }else if (document.attachEvent){
                document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
                document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
            }
        }else{
            jsApiCall();
        }
    })
    //Ajax异步查询订单状态
    var wx_listen, a = true;
    wx_listen = setInterval(function(){
        if(a){
            $.post("<?php echo home_url("/?action=wxpay_notify"); ?>",{
                order_no : "<?php echo order_no; ?>",
                sku : <?php echo intval($_GET["sku"]); ?><?php if(isset($_POST["ticket"])){ ?>,
                ticket : "<?php echo $_POST["ticket"]; ?>"<?php } ?>
            },function(data){
                if(data!==""){
                    $("body").addClass("in").find("#loading").remove();
                    $("#container").html(data);
                    a = false;
                    clearinterval("wx_listen");
                }
            });
        }
    },3000);
});
</script>
<?php
}
function flh_footer_native(){
    global $post, $current_user;
    $timestamp  = current_time("timestamp");
    //用户信息
    $user_id    = $current_user->ID;
    $user_info  = bls_weixin_user_meta($user_id);
    
?>
<div class="weui_dialog_confirm" id="qr_dialog">
    <div class="weui_mask"></div>
    <div class="weui_dialog">
        <div class="weui_dialog_hd"><strong class="weui_dialog_title">请长按三秒或者扫码支付</strong></div>
        <div class="weui_dialog_bd">
            <img class="center-block" alt="" src="<?php echo wx_qr_url; ?>" id="qrcode" >
        </div>
        <div class="weui_dialog_ft">
            <a href="javascript:;" class="weui_btn_dialog default">关闭</a>
        </div>
    </div>
</div>
<!-- //native -->
<script type="text/javascript">
function checkout()
{
    document.getElementsByTagName("body").className = "in";
}
jQuery(function($) {
    $("#checkout-btn").click(function(){
        $("body").addClass("in");
    })
    //Ajax异步查询订单状态
    var wx_listen, a = true;
    wx_listen = setInterval(function(){
        if(a){
            $.post("<?php echo home_url("/?action=wxpay_notify"); ?>",{
                order_no : "<?php echo order_no; ?>",
                sku : <?php echo intval($_GET["sku"]); ?><?php if(isset($_POST["ticket"])){ ?>,
                ticket : "<?php echo $_POST["ticket"]; ?>"<?php } ?>
            },function(data){
                if(data!==""){
                    $("body").addClass("in").find("#loading").remove();
                    $("#container").html(data);
                    a = false;
                    clearinterval("wx_listen");
                }
            });
        }
    },3000);
});
</script>
<?php
}
?>
    <div class="tabbar">
        <div class="weui_tab">
            <div class="weui_tab_bd">
                <div class="weui_panel weui_panel_access">
                    <div class="weui_panel_hd">订单内容</div>
                    <div class="weui_panel_bd game_mode">
                        <a href="<?php echo get_permalink($goods_id); ?>" class="weui_media_box weui_media_appmsg">
                            <div class="weui_media_hd"> <img class="weui_media_appmsg_thumb" src="<?php bls_thumbnail($goods_id); ?>" alt=""> </div>
                            <div class="weui_media_bd">
                                <h4 class="weui_media_title"><?php echo $goods->post_title; ?></h4>
                                <p class="weui_media_desc">热度：<?php
                                    $level = max(0,min(5,get_post_meta($goods_id, "level", true)));
                                    for($i=0;$i<$level;$i++) echo '<i class="weui_icon_fire"></i>';
                                ?></p>
                                <p class="weui_media_desc"><?php
                                    $game = get_post_meta($goods_id, "game", true);
                                    echo count($game);
                                ?>款游戏, <?php echo get_post_meta($goods_id, "price", true); ?>元/<?php
                                    $duration = get_post_meta($goods_id, "duration", true);
                                    echo round($duration,2);
                                ?>分钟</p>
                            </div>
                        </a>
                    </div>
                </div>
                <form class="weui_cells weui_cells_form" method="post">
                  <div class="weui_cell">
                    <div class="weui_cell_bd weui_cell_primary">
                      <p>优惠码</p>
                    </div>
                    <?php
                    if($str!=""){
                    ?>
                    <div class="weui_cell_bd weui_rtl"><em><?php echo $str; ?></em></div>
                    <a href="<?php echo add_query_arg("sku", $sku, checkout_url); ?>" class="weui_cell_ft">
                        <i class="weui_icon_cancel"></i>
                    </a>
                    <?php
                    } else {
                    ?>
                    <div class="weui_cell_bd weui_rtl">
                      <div class="weui_btn_group">
                        <input class="weui_input" name="ticket" value="<?php $desc; ?>">
                        <button type="submit" class="weui_btn weui_btn_mini weui_btn_primary ">使用</button>
                      </div>
                    </div>
                    <?php
                    }
                    ?>
                  </div>
                  <div class="weui_cell">
                    <div class="weui_cell_bd weui_cell_primary">
                      <p>应付总价</p>
                    </div>
                    <div class="weui_cell_bd weui_rtl">
                      <em id="pay"><?php echo $cost; ?> 元</em>
                    </div>
                  </div>
                </form>
                <div class="weui_cells weui_cells_form">
                    <div class="weui_cell">
                        <div class="weui_cell_bd weui_cell_primary">
                            <textarea class="weui_textarea" readonly rows="5">
订购须知
付款成功后，不支持退款
                            </textarea>
                        </div>
                    </div>
                </div>
                <?php get_sidebar(); ?>
            </div>
            <div class="weui_tabbar">
                <button class="weui_tabbar_item weui_btn weui_btn_primary weui_bar_item_on" id="checkout-btn">立即付款</button>
            </div>
        </div>
    </div>
<?php
} else {
    $malls = get_terms("album", array(
        'hide_empty' => false,
    ));
    $mall = current($malls);
    $back = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : get_term_link($mall->term_id);
    wp_redirect($back);
    exit;
}