<?php
/**
 * 功能函数 * 核心
 * ver 1.0 core
**/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*/////////////////////////////////////////////////////////*/
/*@ 远程交互类函数                                           */
/*@ function.php                                           */
/*/////////////////////////////////////////////////////////*/

/*
 * 
**/
if(!function_exists('formatBizQueryParaMap')):
function formatBizQueryParaMap($paraMap, $urlencode){
    $buff = "";
    ksort($paraMap);
    foreach ($paraMap as $k => $v){
        if($urlencode){
           $v = urlencode($v);
        }
        $buff .= $k . "=" . $v . "&";
    }
    $reqPar;
    if (strlen($buff) > 0){
        $reqPar = substr($buff, 0, strlen($buff)-1);
    }
    return $reqPar;
}
endif;

/*
 * 
**/
if(!function_exists('getSign')):
function getSign($Obj){
    foreach ($Obj as $k => $v)
    {
        $Parameters[$k] = $v;
    }
    ksort($Parameters);
    $String = formatBizQueryParaMap($Parameters, false);
    $String = $String."&key=".Mchkey;
    $String = md5($String);
    $result = strtoupper($String);
    return $result;
}
endif;

/*
 * 寰俊鏀粯鐢熸垚绛惧悕
**/
if(!function_exists('getParameters')):
function getParameters($wx_order){
    $jsApiObj["appId"] = $wx_order["appid"];
    $timeStamp = current_time("timestamp");
    $jsApiObj["timeStamp"] = "$timeStamp";
    $jsApiObj["nonceStr"] = $wx_order["nonce_str"];
    $jsApiObj["package"] = "prepay_id=".$wx_order["prepay_id"];
    $jsApiObj["signType"] = "MD5";
    $jsApiObj["paySign"] = getSign($jsApiObj);
    $parameters = json_encode($jsApiObj);    
    return $parameters;
}
endif;

/*
 * 远程获取微信支付统一订单号
**/
if(!function_exists('get_prepay_id')):
function get_prepay_id($trade_type,$order_no,$money,$body,$openid=""){
    $timestamp = current_time("timestamp");
    $wx_order = array(
        "appid" => AppID,
        "mch_id" => Mchid,
        "nonce_str" => bls_rand_str(32),
        "body" => $body,
        "out_trade_no" => $order_no,
        "total_fee" => $money,
        "spbill_create_ip" => $_SERVER['REMOTE_ADDR'],
        "notify_url" => home_url("/?wxpay=notify"),
        "trade_type" => $trade_type,
        "product_id" => $timestamp
    );
    if($trade_type=="JSAPI") $wx_order["openid"] = $openid; 
    $wx_order["sign"] = getSign($wx_order);//签名
    $wx_order_url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
    $remote_arr = wp_remote_post($wx_order_url,array('body' => arrayToXml($wx_order)));
    if ( !is_wp_error( $remote_arr ) ){
        $wx_order = xmlToArray($remote_arr["body"]);
    }
    switch($trade_type){
        default:
        case "NATIVE":
            $wx_order = json_encode($wx_order);
        break;
        case "JSAPI":
            if(isset($wx_order["prepay_id"]))
            $wx_order = getParameters($wx_order);
        break;
        case "WAP":
        
        break;
    }
    return $wx_order;
}
endif;


/*/////////////////////////////////////////////////////////*/
/*@ 数据查询类函数                                           */
/*@ function.php                                           */
/*/////////////////////////////////////////////////////////*/

/*
 * 查询用户完整的数据
 * users-------------------
 * ID
 * user_login
 * user_pass
 * user_nicename
 * user_email
 * user_url
 * user_registered
 * display_name
 * user_meta---------------
 * user_firstname
 * user_lastname
 * nickname
 * description
 * wp_capabilities (array)
 * admin_color (Theme of your admin page. Default is fresh.)
 * closedpostboxes_page
 * primary_blog
 * rich_editing
 * source_domain
 * "number",       //用户唯一序号
 * "openid",       //openid
 * "headimgurl"    //头像url string
 * "tickets",      //优惠券 (array) array(id1=>type1, 卡券号=>类型
**/
if(!function_exists('bls_weixin_user_meta')):
function bls_weixin_user_meta($user_id=false, $meta=true){
    if(!is_numeric($user_id) || $user_id<1){
        global $current_user;
        $user_info = $current_user;
        $user_id   = $user_info->ID;
    } else {
        $user_info = get_userdata($user_id);
    }
    if($user_info && intval($user_id)>0 && $meta){
        //防止报错，先定义关键字段的默认值
        $meta_keys  = array(
            //微信资料*
            "openid"            => "", //openid (string)
            "headimgurl"        => "", //头像url (string)
            "tickets"			=> array()  //优惠券 (array)             
        );
        foreach($meta_keys as $key=>$val){
            $user_info->$key = $val;
        }
        //读取用户的全部meta数据
        $user_meta_date = get_user_meta($user_id);
        foreach($user_meta_date as $user_meta_key=>$user_meta_val){
            $user_info->$user_meta_key = maybe_unserialize($user_meta_val[0]);
        }
        //强制刷新头像&首次自动刷新头像
        $access_token = get_bls_vr_option("access_token","");
        if($user_info->openid != "" && $access_token != "" && (
            $user_info->headimgurl == "" || //如果头像字段为空
            isset($_GET["refresh"]) //如果有refresh参数
        )){
            $openid         = $user_info->openid;
            $get_user_url   = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
            $remote_info    = wp_remote_get( $get_user_url );
            $refresh_data   = json_decode(wp_remote_retrieve_body($remote_info));
            if($refresh_data && isset($refresh_data->headimgurl)){
                //更新头像
                update_user_meta($user_id, 'headimgurl',$refresh_data->headimgurl);
                $user_info->headimgurl = $refresh_data->headimgurl;
            }
        }
    }
    return $user_info;
}
endif;

/*
 * 通过openid查找到用户
**/
if(!function_exists('bls_weixin_user_meta_by_openid')):
function bls_weixin_user_meta_by_openid($openid=false, $meta=true){
    if($openid){
        if(is_object($openid)) $openid = $openid->FromUserName; //获取
        if($meta){
            $user_info_cache = wp_cache_get($openid, 'bls_weixin_user_mix');
            if($user_info_cache){
                return $user_info_cache;
            }
        } else {
            $user_info_cache = wp_cache_get($openid, 'bls_weixin_user');
            if($user_info_cache){
                return $user_info_cache;
            }
        }
        global $wpdb;
        $user_id = $wpdb->get_var("select user_id from {$wpdb->usermeta} where meta_value like '%{$openid}%' ");
        if($user_id){
            $user_info = bls_weixin_user_meta($user_id, $meta);
            if($meta){
                wp_cache_set($openid, $user_info, 'bls_weixin_user_mix');
            } else {
                wp_cache_set($openid, $user_info, 'bls_weixin_user');
            }
            return $user_info;
        }
    }
    return false;
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

/*
 * 查询 $pid 的缩略图
 * 带直接输出函数
**/
if(!function_exists('get_bls_thumbnail')):
function get_bls_thumbnail($pid = NULL,$nopic = NULL,$agrs = 'thumbnail'){
	if($pid == NULL) $pid=get_the_ID();
  $default = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48c3ZnIHZlcnNpb249IjEuMSIgaWQ9ImxheSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiICB2aWV3Qm94PSItMjYyIDI3OCA0MDAgMjk5LjgiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgLTI2MiAyNzggNDAwIDI5OS44OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+PHN0eWxlIHR5cGU9InRleHQvY3NzIj4gLnN0MHtmaWxsOiNFRkVGRUY7fS5zdDF7ZmlsbDojRENEREREO308L3N0eWxlPjxyZWN0IHg9Ii0yNjIiIHk9IjI3OCIgY2xhc3M9InN0MCIgd2lkdGg9IjQwMCIgaGVpZ2h0PSIyOTkuOCIvPjxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik0xMy41LDM1Mi40YzMuOSwzLjksNS42LDguNSw1LjYsMTMuOHYxMjMuNWMwLDUuMy0xLjgsOS45LTUuNiwxMy44UzUsNTA5LTAuMyw1MDloLTEyMy41IGMtNS4zLDAtOS45LTEuOC0xMy44LTUuNmMtMy45LTMuOS01LjYtOC41LTUuNi0xMy44VjM2Ni4yYzAtNS4zLDEuOC05LjksNS42LTEzLjhjMy45LTMuOSw4LjUtNS42LDEzLjgtNS42SC0wLjYgQzUsMzQ2LjgsOS42LDM0OC45LDEzLjUsMzUyLjR6IE0xNiwzNjcuOWMwLTQuOS0xLjgtOS4yLTUuMy0xMi43QzcuMSwzNTEuNywyLjksMzUwLTIsMzUwSC0xMjJjLTQuOSwwLTkuMiwxLjgtMTIuNyw1LjMgYy0zLjUsMy41LTUuMyw3LjgtNS4zLDEyLjd2NjUuM2wxMC45LTEwLjljMy45LTMuOSw4LjUtNS42LDEzLjgtNS42YzUuMywwLDkuOSwxLjgsMTMuOCw1LjZsNDUuNSw0NS41bDI0LjMtMjQuMyBjMy45LTMuOSw4LjUtNS42LDEzLjgtNS42czkuOSwxLjgsMTMuOCw1LjZMMTYsNDYzLjVDMTYsNDYzLjUsMTYsMzY3LjksMTYsMzY3Ljl6IE0tMjIuOCw1MDUuOWwtNzkuNy04MC4xIGMtMy41LTMuNS03LjgtNS4zLTEyLjctNS4zYy00LjksMC05LjIsMS44LTEyLjcsNS4zbC0xMiwxMnY1MC4xYzAsNC45LDEuOCw5LjIsNS4zLDEyLjdjMy41LDMuNSw3LjgsNS4zLDEyLjcsNS4zIEMtMTIyLDUwNS45LTIyLjgsNTA1LjktMjIuOCw1MDUuOXogTTE2LDQ2OC4xTC01LjIsNDQ3Yy0zLjUtMy41LTcuOC01LjMtMTIuNy01LjNzLTkuMiwxLjgtMTIuNyw1LjNsLTIzLjMsMjMuM2wzNS42LDM2SC0yIGM0LjksMCw5LjItMS44LDEyLjctNS4zczUuMy03LjgsNS4zLTEyLjdDMTYsNDg4LjIsMTYsNDY4LjEsMTYsNDY4LjF6IE0tMTEuOSwzOTRjMCw1LjMtMS44LDkuOS01LjYsMTMuOHMtOC41LDUuNi0xMy44LDUuNiBzLTkuOS0xLjgtMTMuOC01LjZjLTMuOS0zLjktNS42LTguNS01LjYtMTMuOGMwLTUuMywxLjgtOS45LDUuNi0xMy44YzMuOS0zLjksOC41LTUuNiwxMy44LTUuNnM5LjksMS44LDEzLjgsNS42IEMtMTMuNywzODMuOC0xMS45LDM4OC40LTExLjksMzk0eiBNLTE1LjEsMzk0YzAtNC42LTEuNC04LjEtNC42LTExLjNjLTMuMi0zLjItNy4xLTQuNi0xMS42LTQuNnMtOC41LDEuNC0xMS42LDQuNiBzLTQuNiw3LjEtNC42LDExLjNjMCw0LjYsMS40LDguMSw0LjYsMTEuM2MzLjIsMy4yLDcuMSw0LjYsMTEuNiw0LjZzOC41LTEuNCwxMS42LTQuNkMtMTYuNSw0MDIuMi0xNS4xLDM5OC4zLTE1LjEsMzk0eiIvPjwvc3ZnPgo=';
  if(!$pid) return $default;
	if( has_post_thumbnail($pid) ){
		//如果有缩略图，则显示缩略图
		$timthumb_src = wp_get_attachment_image_src(get_post_thumbnail_id($pid),$agrs);
		$post_timthumb = $timthumb_src[0];
	} else {
		$content = get_post_field("post_content",$pid);
		//获取日志中第一张图片
		$image_num = preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $img_arr);
		if($image_num > 0 && isset($img_arr[1])){
      //如果日志中有图片
			$post_timthumb = $img_arr[1];
		} else {
			//如果日志中没有图片，则显示默认
      $post_timthumb = empty($nopic) ? $default : $nopic;
		}
	}
	return ($post_timthumb ? $post_timthumb : $default);
}
endif;
if(!function_exists('bls_thumbnail')):
function bls_thumbnail($pid = NULL,$nopic = NULL,$agrs = 'thumbnail'){
	echo get_bls_thumbnail($pid,$nopic,$agrs);
}
endif;

/*/////////////////////////////////////////////////////////*/
/*@ 文件操作类函数                                           */
/*@ function.php                                           */
/*/////////////////////////////////////////////////////////*/

/*
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

/*
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
    
    if ( defined( $key ) && constant( $key ) == maybe_serialize($val)) return true;
    $config = file_exists(ABSPATH.'wp-config.php') ? ABSPATH.'wp-config.php' : dirname(ABSPATH).'/wp-config.php';
	if ( @is_file( $config ) == false ) {
		return false;
	}
	if (!is_writeable_ACLSafe($config)) {
        //如果获取失败则刷新页面
        add_action('admin_notices', 'bls_weixin_update_constant_error_notices');
        function bls_weixin_update_constant_error_notices(){
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
            if ( strpos($line,$old) !== false && strpos($line,"//from bls-weixin") !== false && !$done) {
                //如果$val是空值，我们会直接删除
                if($val!==""){
                    //否则修改为新的值
                    fputs($file, "$new //from bls-weixin\n");
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
                fputs($file, "$new //from bls-weixin\n");
                fputs($file, $line);
                $done = true;
            }
        }
	    fclose($file);
    }
	return true;
}
endif;

/*/////////////////////////////////////////////////////////*/
/*@ 数据转换类函数                                           */
/*@ function.php                                           */
/*/////////////////////////////////////////////////////////*/

/*
 * 转换头像大小为$size
 * 如果为用户id会查询获取头像url
**/
if(!function_exists('get_headimgurl')):
function get_headimgurl($headimgurl=false,$size = 0){
    $str = "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE5LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9ImxheTAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IgoJIHZpZXdCb3g9IjAgMCAxODEuNCAxODEuNCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTgxLjQgMTgxLjQ7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4KPHN0eWxlIHR5cGU9InRleHQvY3NzIj4KCS5zdDB7ZmlsbDojQjVCNUI2O3N0cm9rZTojMDAwMDAwO3N0cm9rZS1taXRlcmxpbWl0OjEwO30KCS5zdDF7ZmlsbDojOUZBMEEwO30KPC9zdHlsZT4KPHJlY3QgaWQ9IlhNTElEXzFfIiB4PSIwIiB5PSIwIiBjbGFzcz0ic3QwIiB3aWR0aD0iMTgxLjQiIGhlaWdodD0iMTgxLjQiLz4KPHBhdGggaWQ9IlhNTElEXzNfIiBjbGFzcz0ic3QxIiBkPSJNNTkuOSw1OS45Yy0zLjIsMC01LjksMS4xLTguMiwzLjRjLTIuMiwyLjItMy40LDUtMy40LDguMmMwLDMuMiwxLjEsNS45LDMuNCw4LjIKCWMyLjIsMi4yLDUsMy40LDguMiwzLjRjMy4yLDAsNS45LTEuMSw4LjItMy40YzIuMi0yLjIsMy40LTUsMy40LTguMmMwLTMuMi0xLjEtNS45LTMuNC04LjJDNjUuOCw2MSw2My4xLDU5LjksNTkuOSw1OS45egoJIE0xMDguMSw2OS41bC0zMC44LDMwLjhsLTkuNi05LjZMNDguMywxMTB2MTEuNmg4NC44di0yN0wxMDguMSw2OS41eiBNMTQwLjMsNTIuN2MwLjQsMC40LDAuNiwwLjgsMC42LDEuNHY3My4yCgljMCwwLjUtMC4yLDEtMC42LDEuNGMtMC40LDAuNC0wLjgsMC42LTEuNCwwLjZINDIuNWMtMC41LDAtMS0wLjItMS40LTAuNmMtMC40LTAuNC0wLjYtMC44LTAuNi0xLjRWNTQuMWMwLTAuNSwwLjItMSwwLjYtMS40CgljMC40LTAuNCwwLjgtMC42LDEuNC0wLjZoOTYuNEMxMzkuNCw1Mi4yLDEzOS45LDUyLjMsMTQwLjMsNTIuN3ogTTE0NS43LDQ3LjNjLTEuOS0xLjktNC4yLTIuOC02LjgtMi44SDQyLjUKCWMtMi43LDAtNC45LDAuOS02LjgsMi44Yy0xLjksMS45LTIuOCw0LjItMi44LDYuOHY3My4yYzAsMi43LDAuOSw0LjksMi44LDYuOGMxLjksMS45LDQuMiwyLjgsNi44LDIuOGg5Ni40YzIuNywwLDQuOS0wLjksNi44LTIuOAoJYzEuOS0xLjksMi44LTQuMiwyLjgtNi44VjU0LjFDMTQ4LjUsNTEuNCwxNDcuNiw0OS4yLDE0NS43LDQ3LjN6Ii8+Cjwvc3ZnPg==";
    if(is_numeric($headimgurl)){
        $headimgurl = get_user_meta($headimgurl, "headimgurl", true);
        if(!$headimgurl && !is_numeric($headimgurl)){
            $str = get_headimgurl($headimgurl,$size);
        }
    } elseif($headimgurl){
        $all_width  = array('0','46','64','96','132');
        $check_key  = 0;
        foreach($all_width as $key=>$val){
            if(abs($size - $val) <= abs($size - $check_key)) $check_key = $val;
        }
        $str = preg_replace("/[0|46|64|96|132]$/",$check_key,$headimgurl);
    }
    return $str;
}
endif;

/*
 * 转换array为xml
**/
if(!function_exists('arrayToXml')):
function arrayToXml($arr){
    $xml = "<xml>";
    foreach ($arr as $key=>$val){
         if (is_array($val)){
            $xml_child = arrayToXml_child($val);
            $xml.="<".$key.">".$xml_child."</".$key.">";
         } elseif (is_numeric($val)){
            $xml.="<".$key.">".$val."</".$key.">";
         } else {
            $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
         }
    }
    $xml.="</xml>";
    return $xml; 
}
endif;
if(!function_exists('arrayToXml_child')):
function arrayToXml_child($arr){
    $xml = "";
    foreach ($arr as $key=>$val){
         if (is_array($val)){
            $xml_child = arrayToXml_child($val);
            $xml.="<".$key.">".$xml_child."</".$key.">";
         }
         elseif (is_numeric($val)){
            $xml.="<".$key.">".$val."</".$key.">";
         } else {
            $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
         }
    }
    return $xml; 
}
endif;

/*
 * 转换array为json 针对微信客服发送消息专用
**/
if(!function_exists('arrayToJson')):
function arrayToJson($arr){
    $json = "{";
    $i = 0;
    foreach ($arr as $key => $val){
        $i ++;
        if (is_array($val)){
            if($key=="articles"){
                $json .= '"'.$key.'":'.arrayToJson_child($val);
            } else {
                $json .= '"'.$key.'":'.arrayToJson($val);
            }
            $json .= $i == count($arr) ? "" : ",";
        } else {
            $json .= '"'.$key.'":"'.$val.'"'.($i == count($arr) ? "" : ",");
        }
    }
    $json .= "}";
    return $json; 
}
endif;
if(!function_exists('arrayToJson_child')):
function arrayToJson_child($arr){
    $json = "[";
    $i = array(-1=>0);
    foreach ($arr as $key => $val){
        $json .= "{";
        $i[-1] ++;
        if (is_array($val)){
            foreach ($val as $key1 => $val1){
                if(isset($i[$key])) $i[$key]++; else $i[$key] = 1;
                $json .= '"'.$key1.'":"'.$val1.'"'.($i[$key] == count($val) ? "" : ",");
            }
        } else {
            $json .= '"'.$key.'":"'.$val.'"'.($i == count($arr) ? "" : ",");
        }
        $json .= "}".($i[-1] == count($arr) ? "" : ",");
    }
    $json .= "]";
    return $json; 
}
endif;

/*
 * 转换xml为array
**/
if(!function_exists('xmlToArray')):
function xmlToArray($xml){
    $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $array_data;
}
endif;

/*/////////////////////////////////////////////////////////*/
/*@ 生成类函数                                              */
/*@ function.php                                           */
/*/////////////////////////////////////////////////////////*/

/*
 * 生成随机字符串
 * $max为false的话，只生成随机纯数字
**/
if(!function_exists('bls_rand_str')):
function bls_rand_str($length=5,$max=true) {
    $possible = "0123456789"."abcdefghijklmnopqrstuvwxyz"."ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    if(is_numeric($max)){
        $length = mt_rand(min($length,intval($max)),max($length,intval($max)));
    } elseif(!$max){
        $possible = "0123456789";
    }
    $str = "";
    while(strlen($str) < $length){
        $str.= substr($possible, (rand() % strlen($possible)), 1);
        $str = max(1,$str);//第一个数字为0时用1替换
    }
    return $str; 
} 
endif;

/*
 * 生成一个独一无二的订单号
 * 时间戳+6位订单自增编号
**/
if(!function_exists('get_order_no')):
function get_order_no(){
    return "DDD0".bls_rand_str(8,false);
}
endif;

/*
 * 生成一个独一无二的票券
**/
if(!function_exists('get_unique_ticket')):
function get_unique_ticket(){
    global $wpdb;
    $unique = true;
    while($unique){
        $ticket = strtolower(bls_rand_str(6));
        $unique = $wpdb->get_var("select ID from {$wpdb->posts} where post_title='{$ticket}' ");
    }
    return $ticket;
}
endif;
if(!function_exists('unique_ticket')):
function unique_ticket(){
    echo get_unique_ticket();
}
endif;

/*
 * 生成一个数组
 * 把一串汉字字符串分割成数组
**/
if(!function_exists('string2array')):
function string2array($str,$charset="utf-8") {
    $strlen = mb_strlen($str);
    $array = array();
    while($strlen){
        $array[] = mb_substr($str, 0, 1, $charset);
        $str = mb_substr($str, 1, $strlen, $charset);
        $strlen = mb_strlen($str);
    }
    return $array;
}
endif;

/*/////////////////////////////////////////////////////////*/
/*@ 逻辑类函数                                              */
/*@ function.php                                           */
/*/////////////////////////////////////////////////////////*/

/*
 * 判断场景page是否存在
 * 存在则返回页面ID
 * 不存在则创建,再返回页面ID
**/
if(!function_exists('bls_weixin_repair_custom_page')):
function bls_weixin_repair_custom_page($agrs,$info="",$type="page"){
    $post_id = 0;
    if( is_array($agrs)){
        $post_title = isset($agrs["post_title"]) ? sanitize_text_field($agrs["post_title"]) : "";
        $post_name = isset($agrs["post_name"]) ? sanitize_text_field($agrs["post_name"]) : "";
        $post_excerpt = isset($agrs["post_excerpt"]) ? sanitize_text_field($agrs["post_excerpt"]) : "";
    } else {
        $post_title = $info;
        $post_name = $agrs;
        $post_excerpt = "";
    }
    $custom_page = get_page_by_title($post_title);
    if($custom_page){
        $post_id = $custom_page->ID;
    } elseif(!empty($post_title) && !empty($post_name)){
        $post = array(
            'post_title'    => $post_title,
            'post_name'     => $post_name,
            'post_excerpt'  => $post_excerpt,
            'post_status'   => 'publish',
            'comment_status'=> 'open',
            'post_type'     => $type
        );
        $post_id = wp_insert_post( $post );
    }
    return $post_id;
}
endif;

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

/*
 * 判断a、b两个值的大小，用于数组键值排序
 * a大于b则返回-1,相等则返回0,小于则返回1
**/
//根据键值升序
function bls_weixin_compare($a, $b){
    if ($a == $b) {
        return 0;
    }
    return ($a > $b) ? -1 : 1;
}

/*
 * 模糊查找boolean
**/
if(!function_exists('bls_weixin_array_strpos')):
function bls_weixin_array_strpos($str="", $array=array(), $mix=false){
    if($str && $array) foreach($array as $key=>$val){
        if(strpos($str,$val) !== false || ($mix && stripos($str,$val) !== false)) return true;
    }
    return false;
}
endif;
/**
 * 生成8位数字
**/
if(!function_exists('get_new_uid')):
function get_new_uid(){
	return bls_rand_str(8,false);
}
endif;
/**
 * 自动登录
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
            $appid      = get_bls_vr_option("appid",false);
            $secret     = get_bls_vr_option("appsecret",false);
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
                                    $access_token = get_bls_vr_option("access_token",false);
                                    if($access_token){
                                        $remote_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
                                        $user_date = json_decode(wp_remote_retrieve_body(wp_remote_get($remote_url)));
                                        //有关注微信公众号
                                        if(isset($user_date->subscribe) && $user_date->subscribe>0){
                                            $user_number= get_new_uid();
                                            $user_login = "wx".$user_number;
                                            $user_id    = wp_insert_user(array(
                                                'user_login'    => $user_login,
                                                'user_pass'     => NULL,
                                                'display_name'  => $user_date->nickname
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
                                            }*/
                                            update_user_meta($user_id, "headimgurl", $user_date->headimgurl);
                                        }
                                    }
                                } else {
                                    $user_number= get_new_uid();
                                    $user_login = "nm".$user_number;
                                    $user_id    = wp_insert_user(array(
                                        'user_login'    => $user_login,
                                        'user_pass'     => NULL
                                    ));
                                }
                                if($user_id>0){
                                    //写入用户基本数据
                                    update_user_meta($user_id, "show_admin_bar_front", "false");
                                    update_user_meta($user_id, "openid", $openid); 
                                    return true;
                                }
                            }
                        }
                    }
                } else {
                    $redirect_url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($url)."&response_type=code&scope=snsapi_base&state=".$timestamp."#wechat_redirect";
                    wp_redirect($redirect_url);
                    exit;
                }
            }
        }
    }
    return false;
}
endif;

/*/////////////////////////////////////////////////////////*/
/*@ 对项目进行读、增、改、删操作函数                           */
/*@ function.php                                           */
/*/////////////////////////////////////////////////////////*/

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

/*
 * 查询一条项目纪录
**/
if (!function_exists('get_bls_object')){
    function get_bls_object($object_id){
        $result = wp_cache_get( $object_id, 'bls_object');
        if(!$result){
            global $wpdb;
            $table_object = $wpdb->prefix . 'object';//项目数据库
            $result = $wpdb->get_row("select * from {$table_object} where object_code='{$object_id}' ", ARRAY_A);
            wp_cache_add( $object_id, $result, 'bls_object');
        }
        return array_map("maybe_unserialize",$result);
	}
}

/*
 * 查询用户的所有项目纪录
**/
if (!function_exists('get_bls_object_by_user_id')){
	function get_bls_object_by_user_id($user_id){
        $result = wp_cache_get( $user_id, 'bls_user_object');
        if(!$result){
            global $wpdb;
            $table_object = $wpdb->prefix . 'object';//项目数据库
		    $user_id = is_object($user_id) ? $user_id->ID : intval($user_id);
            $result = $wpdb->get_results("select * from {$table_object} where user_id='{$user_id}' ", ARRAY_A);
            wp_cache_add( $user_id, $result, 'bls_user_object');
		}
        return array_map("maybe_unserialize",$result);
	}
}

/*
 * 增加一个项目
**/
if (!function_exists('add_bls_object')):
	function add_bls_object($user_id=false,$arg=array()){
        if(!$user_id) return false;
        global $wpdb;
        $timestamp         = current_time("timestamp");
        $table_object      = $wpdb->prefix . 'object';     //项目数据库
        $object            = array(
            'user_id'           => intval($user_id), //归属的用户ID
            'object_order_no'   => isset($arg["object_order_no"]) ? ($arg["object_order_no"]) : get_order_no(), //订单编号
            'object_order_time' => isset($arg["object_order_time"]) ? ($arg["object_order_time"]) : $timestamp, //订单时间戳          
            'object_goods'      => isset($arg["object_goods"]) ? maybe_serialize($arg["object_goods"]) : "", //购买套餐 post_id
            'object_price'      => isset($arg["object_price"]) ? $arg["object_price"] : "", //支付总金额
            
            'object_cost'       => isset($arg["object_cost"]) ? $arg["object_cost"] : "", //实际支付金额
            'object_ticket'     => isset($arg["object_ticket"]) ? maybe_serialize($arg["object_ticket"]) : "", //使用的票券 post_title          
            'object_note'       => isset($arg["object_note"]) ? $arg["object_note"] : "", //票券使用描述:抵扣10元
            'object_code'       => isset($arg["object_code"]) ? $arg["object_code"] : "", //预约码 post_title
            'object_status'     => isset($arg["object_status"]) ? ($arg["object_status"]) : "" //状态 0待用 1已经使用
        );
        $wpdb->insert($table_object, $object,array(
            '%d','%s','%s','%s','%s',
            '%s','%s','%s','%s','%s') 
        );
        $object_id = $wpdb->insert_id;
        $object["object_id"] = $object_id;
        wp_cache_add( $object_id, $object, 'bls_object');
        return $object_id;
	}
endif;

/*
 * 更新一个项目
**/
if (!function_exists('update_bls_object')):
	function update_bls_object($object_id=false,$arg=array()){
        if(!$object_id) return false;
        $result = wp_cache_get( $object_id, 'bls_object');
        if($result){
            $result = array_merge(maybe_unserialize($result),$arg);
            wp_cache_set($object_id, $result, 'bls_object');
        }
        global $wpdb;
        $table_object      = $wpdb->prefix . 'object';     //项目数据库
        $type = array();
        for($i=0;$i<count($arg);$i++){
            $type[] = '%s';
        }
        return $wpdb->update(
            $table_object,
            array_map("maybe_serialize",$arg),
            array( 'object_id' => $object_id ),
            $type,
            array( '%d' )
        );        
    }
endif;

/*
 * 删除一个项目
**/
if (!function_exists('delete_bls_object')):
	function delete_bls_object($object_id=false){
        if(!$object_id) return false;
        $result = wp_cache_get( $object_id, 'bls_object');
        if($result){
            wp_cache_delete($object_id, 'bls_object');
        }
        global $wpdb;
        $table_object      = $wpdb->prefix . 'object';     //项目数据库
        return $wpdb->query("delete from {$table_object} where object_id='{$object_id}'");
    }
endif;

/*/////////////////////////////////////////////////////////*/
/*@ 有直接输出的函数                                         */
/*@ function.php                                           */
/*/////////////////////////////////////////////////////////*/

/**
 * 主题链接
**/
function bls_vr_url($str=""){
    return bls_vr_url.$str;
}
function bls_vr($str=""){
    echo bls_vr_url($str);
}
function get_bls_vr_dir($str=""){
    return bls_vr_dir.$str;
}

/**
 * 给文章计数
**/
if(!function_exists('flh_count')):
function flh_count( $post_id=0, $count="view", $number=false, $echo=false ) {
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

/*
 * 输出提示信息页面
**/
if(!function_exists('bls_weixin_remote_error_notices')):
function bls_weixin_remote_error_notices($str=false){
    $str = $str ? $str : "微信服务器连接失败，请修复!";
    echo '<div class="wrap nosubsub"><div class="error below-h2"><p><strong> '.$str.' </strong></p></div></div>';
}
endif;

/*
 * 输出用户评论
**/
if(!function_exists('bls_vr_comment')):
function bls_vr_comment($comment, $args, $depth) {
?>
     <a href="javascript:void(0);" class="weui_media_box weui_media_appmsg">
      <div class="weui_media_hd">
          <?php if($comment->user_id>0) echo '<img class="weui_media_appmsg_thumb" src="'.get_headimgurl($comment->user_id).'" alt="">'; else echo get_avatar( $comment ); ?>
      </div>
      <div class="weui_media_bd">
          <h4 class="weui_media_title"><?php echo get_comment_author(); ?><br><small class="weui_media_desc"><?php echo get_comment_date("Y-m-d"); ?> <?php echo get_comment_time("H:i:s"); ?></small></h4>
          <p><?php comment_text(); ?></p>
      </div>
  </a>
<?php
}
endif;

/**
 * Change code lost you hands
**/