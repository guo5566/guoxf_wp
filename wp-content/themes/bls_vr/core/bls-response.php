<?php
/**
 * 微信API * 核心
 * ver 1.0 core
**/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * a、验证微信服务器
 * b、微信消息对接API
 */
add_action('init', 'bls_weixin_response_fn', 9);
function bls_weixin_response_fn() {
    //验证微信服务器
    if(isset($_REQUEST["echostr"])){
        $signature  = $_GET["signature"];
        $echoStr    = $_GET["echostr"];
        $timestamp  = $_GET["timestamp"];
        $nonce      = $_GET["nonce"];
        $tmpArr     = array(AppToken, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr     = implode( $tmpArr );
        $tmpStr     = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            echo $echoStr;
        } else {
           echo "";
        }
        exit;
    }
    //消息接收 微信服务器会POST一串XML过来
    if(isset($GLOBALS["HTTP_RAW_POST_DATA"]) && !empty($GLOBALS["HTTP_RAW_POST_DATA"])){
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if(MsgType > 1 && isset($_GET['msg_signature'])){
            //加密模式下先进行解码
            $msg            = '';
            $msg_signature  = $_GET['msg_signature'];
            $nonce          = $_GET["nonce"];
            $timeStamp      = $_GET["timestamp"];
            include_once('wxmsg/wxBizMsgCrypt.php');
            $pc = new WXBizMsgCrypt(AppToken, EncodingAESKey, AppID);
            $errCode = $pc->decryptMsg($msg_signature, $timeStamp, $nonce, $postStr, $msg);
            if ($errCode == 0) $postStr = $msg;
            else die('');
        }
        $parser  = xml_parser_create();
        if(xml_parse($parser,$postStr)){
            $postObj = simplexml_load_string($postStr);
            /**
             * 响应微信回调
             * 接收保存微信订单的反馈数据
            **/
            //微信支付回调处理
            if(isset($postObj->return_code)){
                global $wpdb;
                if ($postObj->return_code == "FAIL") {
                   //通信出错
                }
                elseif($postObj->result_code == "FAIL"){
                   //业务出错
                } else{
                    //支付成功 写入数据及推送微信消息
                    $table_msg      = $wpdb->prefix . 'msg';        //通信存储
                    $table_msg_meta = $wpdb->prefix . 'msg_meta';   //通信存储meta
                    $order_no = $postObj->out_trade_no;
                    //如果订单已经纪录则不操作
                    $is_order = $wpdb->get_var("select msg_id from {$table_msg_meta} where msg_key='out_trade_no' and msg_value='{$order_no}' ");
                    if($is_order && $is_order>0){
                        //已经执行过了
                    } else {
                        //纪录本次全部信息
                        $data = array(
                            'msg_to_user'   => get_bls_vr_option("wxid"),
                            'msg_from_user' => $postObj->openid,
                            'msg_type'      => "wxpay",
                            'msg_time'      => current_time("timestamp")
                        );
                        $wpdb->insert( 
                            $table_msg, 
                            $data, 
                            array(
                                '%s',
                                '%s',
                                '%s',
                                '%s' 
                            ) 
                        );
                        $msg_id = $wpdb->insert_id;    
                        foreach($postObj as $key=>$val){
                            if(is_array($val)){
                                $val = serialize($val);
                            }
                            if( !in_array($key,array('ToUserName','FromUserName','MsgType','CreateTime','Encrypt')) ){
                                $wpdb->insert( 
                                    $table_msg_meta, 
                                    array(
                                        'msg_id'    => $msg_id,
                                        'msg_key'   => $key,
                                        'msg_value' => $val
                                    ), 
                                    array( 
                                        '%d',
                                        '%s',
                                        '%s',
                                    ) 
                                );
                            }
                        }
                    }
                }
                echo arrayToXml(array("return_code"=>"SUCCESS","return_msg"=>"OK"));
                exit;
            }
            echo " ";
            exit;
        }
    }
}

/**
 * Change code lost you hands
**/