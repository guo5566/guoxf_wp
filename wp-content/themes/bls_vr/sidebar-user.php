<?php
global $current_user,$wpdb;
$table_object = $wpdb->prefix . 'object';
$week = array("日","一","二","三","四","五","六");
if(isset($_GET["ticket"])){
    $ticket = $_GET["ticket"];
    $object = get_bls_object($ticket);
    if($object) {
        $goods_id = $object["object_goods"];
        $w = date("w", $object["object_order_time"]);
        $object_code = $object["object_code"];
        include_once(bls_vr_dir."core/qrcode/qrlib.php");
        //$object_code1="http://127.0.0.1/guoxf_wp/wp-admin/admin.php?page=qrcheckcode&checkcode=".$object_code;
        //http://vr.bstarx.com/wp-admin/
        $object_code1="http://vr.bstarx.com/wp-admin/admin.php?page=qrcheckcode&checkcode=".$object_code;
        $object_code1_img=$object_code;
        
        //QRcode::png($object_code, bls_vr_dir.'core/qrcode/'.$object_code.'.png', QR_ECLEVEL_L, 5);
        QRcode::png($object_code1, bls_vr_dir.'core/qrcode/'.$object_code1_img.'.png', QR_ECLEVEL_L, 5);
?>
    <div class="tabbar">
        <form method="get" class="weui_tab" action="<?php echo user_url; ?>">
            <div class="weui_tab_bd">
                <div class="weui_msg" id="user_panel">
                    <div class="weui_icon_area">
                        <h2 class="weui_msg_title"><em><?php echo date("订购日期：Y-m-d", $object["object_order_time"])." (周".$week[$w].")"; ?></em><br>订单编号：<?php echo $object["object_order_no"]; ?></h2>
                        <img width="200" height="200" src="<?php echo bls_vr_url.'core/qrcode/'.$object_code1_img.'.png'; ?>" alt="">
                        <p class="weui_msg_desc">预约码： <em><?php echo $object_code; ?></em></p>
                    </div>
                    <div class="weui_panel weui_panel_access">
                        <div class="weui_panel_bd game_mode">
                            <a href="<?php echo get_permalink($goods_id); ?>" class="weui_media_box weui_media_appmsg">
                                <div class="weui_media_hd"> <img class="weui_media_appmsg_thumb" src="<?php bls_thumbnail($goods_id); ?>" alt=""> </div>
                                <div class="weui_media_bd">
                                    <h4 class="weui_media_title"><?php echo get_the_title($goods_id); ?></h4>
                                    <p class="weui_media_desc">
                                        <?php
                                        $game = get_post_meta($goods_id, "game", true);
                                        echo count($game);
                                        ?> 款游戏, <?php echo get_post_meta($goods_id, "price", true); ?>元/ <?php
                                        $duration = get_post_meta($goods_id, "duration", true);
                                        echo round($duration,2);
                                        ?> 分钟
                                    </p>
                                    <p class="weui_media_desc">
                                        总价：<?php echo $object["object_price"]; ?>元 / 
                                        支付：<?php echo $object["object_cost"]; ?>元
                                    </p>
                                </div>
                            </a> 
                        </div>
                    </div>
                    <div class="weui_panel">
                        <div class="weui_panel_bd">
                            <div class="weui_media_box weui_media_small_appmsg">
                                <div class="weui_cells weui_cells_access">
                                    <div class="weui_cell">
                                        <div class="weui_cell_hd">体验馆：</div>
                                        <div class="weui_cell_bd weui_cell_primary">
                                            <p><?php echo get_bls_vr_option("shop"); ?></p>
                                        </div>
                                    </div>
                                    <div class="weui_cell">
                                        <div class="weui_cell_hd">地址：</div>
                                        <div class="weui_cell_bd weui_cell_primary">
                                            <p><?php echo get_bls_vr_option("address"); ?></p>
                                        </div>
                                    </div>
                                    <div class="weui_cell">
                                        <div class="weui_cell_hd">手机：</div>
                                        <div class="weui_cell_bd weui_cell_primary">
                                            <p><?php echo get_bls_vr_option("phone"); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="weui_tabbar">
                <button type="submit" class="weui_tabbar_item weui_btn weui_btn_primary weui_bar_item_on" id="checkout-btn">查看我的订单</button>
            </div>
        </form>
    </div>
<?php
    } else {
?>
<?php
    }
} else {
    $user_id = $current_user->ID;
    $tickets = array_reverse(get_user_meta($user_id, "tickets", true));//优惠券
?>
<div id="user_panel">
<?php
    foreach($tickets as $ticket=>$type){
        $object= get_bls_object($ticket);
        $goods_id = $object["object_goods"];
        $w = date("w", $object["object_order_time"]);
        $title = "<strong><em>".date("订购日期：Y-m-d", $object["object_order_time"])." (周".$week[$w].")</em></strong>".'<i class="weui_right weui_icon_qr"></i>';
        $class = " weui_panel_valid";
        $link  = add_query_arg("ticket", $ticket, user_url);
        if($object["object_status"]){
            $class = " weui_panel_used";
            $link = get_permalink($goods_id);
            $items = $wpdb->get_var("select post_excerpt from {$wpdb->posts} where post_type='ticket' and post_title='{$ticket}' ");
            $post_excerpt = maybe_unserialize($items);
            $item = end($post_excerpt);
            $title = "<strong>".date("已于Y-m-d使用", $item["used_time"])."</strong>".'<i class="weui_right weui_icon_used"></i>';
        }
    ?>
    <div class="weui_panel weui_panel_access">
        <div class="weui_panel_hd"><?php echo $title; ?></div>
        <div class="weui_panel_bd game_mode">
            <a href="<?php echo $link; ?>" class="weui_media_box weui_media_appmsg">
                <div class="weui_media_hd"> <img class="weui_media_appmsg_thumb" src="<?php bls_thumbnail($goods_id); ?>" alt=""> </div>
                <div class="weui_media_bd">
                    <h4 class="weui_media_title"><?php echo get_the_title($goods_id); ?></h4>
                    <p class="weui_media_desc">
                        <?php
                        $game = get_post_meta($goods_id, "game", true);
                        echo count($game);
                        ?> 款游戏, <?php echo get_post_meta($goods_id, "price", true); ?>元/ <?php
                        $duration = get_post_meta($goods_id, "duration", true);
                        echo round($duration,2);
                        ?> 分钟
                    </p>
                    <p class="weui_media_desc">
                        总价：<?php echo $object["object_price"]; ?>元 / 
                        支付：<?php echo $object["object_cost"]; ?>元
                    </p>
                </div>
            </a> 
        </div>
    </div>
<?php
    }
?>
</div>
<?php
}