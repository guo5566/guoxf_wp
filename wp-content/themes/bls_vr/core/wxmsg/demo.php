<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="UTF-8" />
</head>
<body><font color="#120102"><pre><?php
header("Content-type: text/html; charset=utf-8");


include_once "wxBizMsgCrypt.php";
$postStr = '<xml>
    <ToUserName><![CDATA[gh_b555dd4b6293]]></ToUserName>
    <Encrypt><![CDATA[9EGl4EmNVn3vmPRnEmaajEovHkh8w8EvjV0Axwlkzvox2QUwvj4cYhgDkbg2yvu4bMO2JHWaGg+2HhQgSEZV0gQMBcTbYgQONlov18ChEciFtzNa4sHCgQF8LjWt1x8OD5cmCiywWBn3sMlLGPXYlCmRlysny1cO/AruBythhHuO96mVbRXvuwRbK+uD3rWEdt0elZvDo4cxnTALcjuQg5xge67sUTowQ0oIY6FMURA2KTbQGG6nwgSKvHd2+VNy5m1JrVh3G/lY8kIN5Y1yV14Ot0EVVQZdaCbSpzx1fmztBAJpeAg8T1aaCdIJ+K9267ZaLgNP9Z3mZZeo9CmC8YTntn+Px5bapxmteFm7l+Mi9ehXBYz/Xm5Z4U6T8y0HwA1fULmEZHlBys7qjqJXLu3ZP8TogYiYE2stlG87q2k=]]></Encrypt>
</xml>';
$xml_tree = new DOMDocument();
$xml_tree->loadXML($postStr);
$array_e = $xml_tree->getElementsByTagName('Encrypt');
$encrypt = $array_e->item(0)->nodeValue;
print_r($encrypt);
/*
$parser  = xml_parser_create();
        if(!empty($postStr) && xml_parse($parser,$postStr)){
            $postObj = simplexml_load_string($postStr);
            //如果存在Encrypt，则采用解码
            if(isset($postObj->Encrypt)){
                $encrypt = $postObj->Encrypt->nodeValue;
print_r($encrypt);
            }
        }
*/

                exit;
// 第三方发送消息给公众平台
$encodingAesKey = "CJUFknij2PYZ2X8VoTcBoXkhAhspgJgfYTEhw9iJW5C";
$token = "8rOtipaPMJENSjqz";
$timeStamp = time();
$nonce = "xxxxxx";
$appId = "wx927e62ed2608cdd4";
$text = "<xml>
    <ToUserName><![CDATA[gh_b555dd4b6293]]></ToUserName>
    <FromUserName><![CDATA[oVb1CuHGMGH_UUEEbgJCLKNVvWXU]]></FromUserName>
    <CreateTime>1427449597</CreateTime>
    <MsgType><![CDATA[text]]></MsgType>
    <Content><![CDATA[/::P]]></Content>
    <MsgId>6130849336007311552</MsgId>
    <Encrypt><![CDATA[HA0mJGzfx+Ckme/19NwIS6SpYJGLnrfGxul3zkI/Hm2oiYR1t0gV/7XxfwKzIXRpPhwHjoc+mdtKmt50j1+qQSgjo4hnTgeyh92EPfaEVtfo/UKK+40OWB8Ggy4Y+j6nokbbVkE/ykId8S5/csj1aRtvFoBRHzL2QNFFLyvcLHR/kB2R6KP06D1R+xoGhdfEbtcqpfsfW6auLL+2iaAf0LmdA546rDixbIPur9oPav86T6JqzOnDrVwju6sDBMX1V1xB26cn14RBg85PEz/qrCCMOL5q8nPbOk76qAn0SeG3ZCPUDWgVREyAVMwrODuwPNDXJ10vIvBsig5F4ZN7UzzuvBuLdOELz329XN0C76FpiHj6yfsSWM3lKqn8ANYMreLQQVNDIYwA92kaT+iQUhioHvvyGDuoF5Y3BE//n2A=]]></Encrypt>
</xml>";


$pc = new WXBizMsgCrypt($token, $encodingAesKey, $appId);
$encryptMsg = '';
$errCode = $pc->encryptMsg($text, $timeStamp, $nonce, $encryptMsg);
if ($errCode == 0) {
	print("加密后: " . $encryptMsg . "\n");
} else {
	print($errCode . "\n");
}

$xml_tree = new DOMDocument();
$xml_tree->loadXML($text);
$array_e = $xml_tree->getElementsByTagName('Encrypt');
$array_s = $xml_tree->getElementsByTagName('MsgSignature');
$encrypt = $array_e->item(0)->nodeValue;
$msg_sign = $array_s->item(0)->nodeValue;

$format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
$from_xml = sprintf($format, $encrypt);

// 第三方收到公众号平台发送的消息
$msg = '';
$errCode = $pc->decryptMsg($msg_sign, $timeStamp, $nonce, $from_xml, $msg);
if ($errCode == 0) {
	print("解密后: " . $msg . "\n");
} else {
	print($errCode . "\n");
}
?></pre></font></body></html>