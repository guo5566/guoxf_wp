<?php
/*
Plugin Name: BLS小插件
Plugin URI: http://www.bluelass.com
Description: 安装BLS系列插件、主题前建议安装本插件！
Version: 1.0
Author URI: http://www.bluelass.com/
*/

if (!function_exists('bls_deregister_admin_bar')):
/*
 * 修改顶部工具条版权
**/
function bls_deregister_admin_bar( $wp_admin_bar ){
    $wp_admin_bar->remove_node( 'wp-logo' );
}
add_action( 'admin_bar_menu', 'bls_deregister_admin_bar', 999 );
endif;


if (!function_exists('bls_hide_login_head')):
/*
 * 修改登录界面logo
**/
function bls_hide_login_head() {
    echo '<style type="text/css">body.login #login h1 a,.login h1 a{display:none;}</style>';
}
add_action("login_head", "bls_hide_login_head");
endif;


add_filter('login_headerurl', create_function(false,"return home_url();"));
add_filter('login_headertitle', create_function(false,"return get_bloginfo('description');"));


if (!function_exists('bls_deregister_open_sans')):
/*
 * 主题后台输出样式脚本
**/
function bls_deregister_open_sans(){
    $locale = get_locale();
    if($locale=="zh_CN"){
        //大陆不推荐使用谷歌字体
        wp_deregister_style( 'open-sans' );
        wp_register_style( 'open-sans', false );
        wp_enqueue_style( 'open-sans','');
    }
}
add_action('admin_enqueue_scripts', 'bls_deregister_open_sans' );
endif;


if (!function_exists('bls_admin_footer_text')):
function bls_admin_footer_text() {
    return '欢迎使用'.get_bloginfo("name").'管理平台';
}
add_filter('admin_footer_text', 'bls_admin_footer_text', 99);
endif;


if (!function_exists('bls_update_footer')):
function bls_update_footer() {
    return '当前登录IP: '.$_SERVER["REMOTE_ADDR"];
}
add_filter('update_footer', 'bls_update_footer', 99);
endif;


if (!function_exists('custom_upload_filter')):
/*
 * 修改中文附件名称为拼音
**/
function custom_upload_filter( $file ){
    $file['name'] = bls_pinyin::encode($file['name']);
    return $file;
}
add_filter('wp_handle_upload_prefilter', 'custom_upload_filter' );
endif;


if (!class_exists('bls_pinyin')):
class bls_pinyin{
	/*
	 * 拼音字符转换图
	 * @var array
	**/
	private static $_aMaps = array(
		'a'=>-20319,'ai'=>-20317,'an'=>-20304,'ang'=>-20295,'ao'=>-20292,
		'ba'=>-20283,'bai'=>-20265,'ban'=>-20257,'bang'=>-20242,'bao'=>-20230,'bei'=>-20051,'ben'=>-20036,'beng'=>-20032,'bi'=>-20026,'bian'=>-20002,'biao'=>-19990,'bie'=>-19986,'bin'=>-19982,'bing'=>-19976,'bo'=>-19805,'bu'=>-19784,
		'ca'=>-19775,'cai'=>-19774,'can'=>-19763,'cang'=>-19756,'cao'=>-19751,'ce'=>-19746,'ceng'=>-19741,'cha'=>-19739,'chai'=>-19728,'chan'=>-19725,'chang'=>-19715,'chao'=>-19540,'che'=>-19531,'chen'=>-19525,'cheng'=>-19515,'chi'=>-19500,'chong'=>-19484,'chou'=>-19479,'chu'=>-19467,'chuai'=>-19289,'chuan'=>-19288,'chuang'=>-19281,'chui'=>-19275,'chun'=>-19270,'chuo'=>-19263,'ci'=>-19261,'cong'=>-19249,'cou'=>-19243,'cu'=>-19242,'cuan'=>-19238,'cui'=>-19235,'cun'=>-19227,'cuo'=>-19224,
		'da'=>-19218,'dai'=>-19212,'dan'=>-19038,'dang'=>-19023,'dao'=>-19018,'de'=>-19006,'deng'=>-19003,'di'=>-18996,'dian'=>-18977,'diao'=>-18961,'die'=>-18952,'ding'=>-18783,'diu'=>-18774,'dong'=>-18773,'dou'=>-18763,'du'=>-18756,'duan'=>-18741,'dui'=>-18735,'dun'=>-18731,'duo'=>-18722,
		'e'=>-18710,'en'=>-18697,'er'=>-18696,
		'fa'=>-18526,'fan'=>-18518,'fang'=>-18501,'fei'=>-18490,'fen'=>-18478,'feng'=>-18463,'fo'=>-18448,'fou'=>-18447,'fu'=>-18446,
		'ga'=>-18239,'gai'=>-18237,'gan'=>-18231,'gang'=>-18220,'gao'=>-18211,'ge'=>-18201,'gei'=>-18184,'gen'=>-18183,'geng'=>-18181,'gong'=>-18012,'gou'=>-17997,'gu'=>-17988,'gua'=>-17970,'guai'=>-17964,'guan'=>-17961,'guang'=>-17950,'gui'=>-17947,'gun'=>-17931,'guo'=>-17928,
		'ha'=>-17922,'hai'=>-17759,'han'=>-17752,'hang'=>-17733,'hao'=>-17730,'he'=>-17721,'hei'=>-17703,'hen'=>-17701,'heng'=>-17697,'hong'=>-17692,'hou'=>-17683,'hu'=>-17676,'hua'=>-17496,'huai'=>-17487,'huan'=>-17482,'huang'=>-17468,'hui'=>-17454,'hun'=>-17433,'huo'=>-17427,
		'ji'=>-17417,'jia'=>-17202,'jian'=>-17185,'jiang'=>-16983,'jiao'=>-16970,'jie'=>-16942,'jin'=>-16915,'jing'=>-16733,'jiong'=>-16708,'jiu'=>-16706,'ju'=>-16689,'juan'=>-16664,'jue'=>-16657,'jun'=>-16647,
		'ka'=>-16474,'kai'=>-16470,'kan'=>-16465,'kang'=>-16459,'kao'=>-16452,'ke'=>-16448,'ken'=>-16433,'keng'=>-16429,'kong'=>-16427,'kou'=>-16423,'ku'=>-16419,'kua'=>-16412,'kuai'=>-16407,'kuan'=>-16403,'kuang'=>-16401,'kui'=>-16393,'kun'=>-16220,'kuo'=>-16216,
		'la'=>-16212,'lai'=>-16205,'lan'=>-16202,'lang'=>-16187,'lao'=>-16180,'le'=>-16171,'lei'=>-16169,'leng'=>-16158,'li'=>-16155,'lia'=>-15959,'lian'=>-15958,'liang'=>-15944,'liao'=>-15933,'lie'=>-15920,'lin'=>-15915,'ling'=>-15903,'liu'=>-15889,'long'=>-15878,'lou'=>-15707,'lu'=>-15701,'lv'=>-15681,'luan'=>-15667,'lue'=>-15661,'lun'=>-15659,'luo'=>-15652,
		'ma'=>-15640,'mai'=>-15631,'man'=>-15625,'mang'=>-15454,'mao'=>-15448,'me'=>-15436,'mei'=>-15435,'men'=>-15419,'meng'=>-15416,'mi'=>-15408,'mian'=>-15394,'miao'=>-15385,'mie'=>-15377,'min'=>-15375,'ming'=>-15369,'miu'=>-15363,'mo'=>-15362,'mou'=>-15183,'mu'=>-15180,
		'na'=>-15165,'nai'=>-15158,'nan'=>-15153,'nang'=>-15150,'nao'=>-15149,'ne'=>-15144,'nei'=>-15143,'nen'=>-15141,'neng'=>-15140,'ni'=>-15139,'nian'=>-15128,'niang'=>-15121,'niao'=>-15119,'nie'=>-15117,'nin'=>-15110,'ning'=>-15109,'niu'=>-14941,'nong'=>-14937,'nu'=>-14933,'nv'=>-14930,'nuan'=>-14929,'nue'=>-14928,'nuo'=>-14926,
		'o'=>-14922,'ou'=>-14921,
		'pa'=>-14914,'pai'=>-14908,'pan'=>-14902,'pang'=>-14894,'pao'=>-14889,'pei'=>-14882,'pen'=>-14873,'peng'=>-14871,'pi'=>-14857,'pian'=>-14678,'piao'=>-14674,'pie'=>-14670,'pin'=>-14668,'ping'=>-14663,'po'=>-14654,'pu'=>-14645,
		'qi'=>-14630,'qia'=>-14594,'qian'=>-14429,'qiang'=>-14407,'qiao'=>-14399,'qie'=>-14384,'qin'=>-14379,'qing'=>-14368,'qiong'=>-14355,'qiu'=>-14353,'qu'=>-14345,'quan'=>-14170,'que'=>-14159,'qun'=>-14151,
		'ran'=>-14149,'rang'=>-14145,'rao'=>-14140,'re'=>-14137,'ren'=>-14135,'reng'=>-14125,'ri'=>-14123,'rong'=>-14122,'rou'=>-14112,'ru'=>-14109,'ruan'=>-14099,'rui'=>-14097,'run'=>-14094,'ruo'=>-14092,
		'sa'=>-14090,'sai'=>-14087,'san'=>-14083,'sang'=>-13917,'sao'=>-13914,'se'=>-13910,'sen'=>-13907,'seng'=>-13906,'sha'=>-13905,'shai'=>-13896,'shan'=>-13894,'shang'=>-13878,'shao'=>-13870,'she'=>-13859,'shen'=>-13847,'sheng'=>-13831,'shi'=>-13658,'shou'=>-13611,'shu'=>-13601,'shua'=>-13406,'shuai'=>-13404,'shuan'=>-13400,'shuang'=>-13398,'shui'=>-13395,'shun'=>-13391,'shuo'=>-13387,'si'=>-13383,'song'=>-13367,'sou'=>-13359,'su'=>-13356,'suan'=>-13343,'sui'=>-13340,'sun'=>-13329,'suo'=>-13326,
		'ta'=>-13318,'tai'=>-13147,'tan'=>-13138,'tang'=>-13120,'tao'=>-13107,'te'=>-13096,'teng'=>-13095,'ti'=>-13091,'tian'=>-13076,'tiao'=>-13068,'tie'=>-13063,'ting'=>-13060,'tong'=>-12888,'tou'=>-12875,'tu'=>-12871,'tuan'=>-12860,'tui'=>-12858,'tun'=>-12852,'tuo'=>-12849,
		'wa'=>-12838,'wai'=>-12831,'wan'=>-12829,'wang'=>-12812,'wei'=>-12802,'wen'=>-12607,'weng'=>-12597,'wo'=>-12594,'wu'=>-12585,
		'xi'=>-12556,'xia'=>-12359,'xian'=>-12346,'xiang'=>-12320,'xiao'=>-12300,'xie'=>-12120,'xin'=>-12099,'xing'=>-12089,'xiong'=>-12074,'xiu'=>-12067,'xu'=>-12058,'xuan'=>-12039,'xue'=>-11867,'xun'=>-11861,
		'ya'=>-11847,'yan'=>-11831,'yang'=>-11798,'yao'=>-11781,'ye'=>-11604,'yi'=>-11589,'yin'=>-11536,'ying'=>-11358,'yo'=>-11340,'yong'=>-11339,'you'=>-11324,'yu'=>-11303,'yuan'=>-11097,'yue'=>-11077,'yun'=>-11067,
		'za'=>-11055,'zai'=>-11052,'zan'=>-11045,'zang'=>-11041,'zao'=>-11038,'ze'=>-11024,'zei'=>-11020,'zen'=>-11019,'zeng'=>-11018,'zha'=>-11014,'zhai'=>-10838,'zhan'=>-10832,'zhang'=>-10815,'zhao'=>-10800,'zhe'=>-10790,'zhen'=>-10780,'zheng'=>-10764,'zhi'=>-10587,'zhong'=>-10544,'zhou'=>-10533,'zhu'=>-10519,'zhua'=>-10331,'zhuai'=>-10329,'zhuan'=>-10328,'zhuang'=>-10322,'zhui'=>-10315,'zhun'=>-10309,'zhuo'=>-10307,'zi'=>-10296,'zong'=>-10281,'zou'=>-10274,'zu'=>-10270,'zuan'=>-10262,'zui'=>-10260,'zun'=>-10256,'zuo'=>-10254
	);

    /*
     * 将中文编码成拼音
     * @param string $utf8Data utf8字符集数据
     * @param string $sRetFormat 返回格式 [true:首字母|false:全拼音]
     * @return string
    **/
	public static function encode($utf8Data, $sRetFormat=false){
		$sGBK = iconv('UTF-8', 'GBK', $utf8Data);
		$aBuf = array();
		for ($i=0, $iLoop=strlen($sGBK); $i<$iLoop; $i++) {
			$iChr = ord($sGBK{$i});
			if ($iChr>160)
				$iChr = ($iChr<<8) + ord($sGBK{++$i}) - 65536;
			if ($sRetFormat)
				$aBuf[] = substr(self::zh2py($iChr),0,1);
			else
				$aBuf[] = self::zh2py($iChr);
		}
        $string = "";
        foreach($aBuf as $key=>$val){
            $string .= $val;
            if(strlen($val)>1 && $key+1<count($aBuf)) $string .= "-";
        }
        return $string;
	}

	/*
	 * 中文转换到拼音(每次处理一个字符)
	 * @param number $iWORD 待处理字符双字节
	 * @return string 拼音
	**/
	private static function zh2py($iWORD) {
		if($iWORD>0 && $iWORD<160 ) {
			return chr($iWORD);
		} elseif ($iWORD<-20319||$iWORD>-10247) {
			return '';
		} else {
			foreach (self::$_aMaps as $py => $code) {
				if($code > $iWORD) break;
				$result = $py;
			}
			return $result;
		}
	}
};
endif;


/*
 * 文章图片本地化
 * 远程链接修改为网址
**/
if (!function_exists('save_remote_image')):
function save_remote_image( $post_id ) {
    if(did_action( 'save_post' ) === 1){
        global $wpdb;
        require_once (ABSPATH . '/wp-admin/includes/media.php');
        require_once (ABSPATH . '/wp-admin/includes/file.php');
        require_once (ABSPATH . '/wp-admin/includes/image.php');
        $siteurl = get_option("siteurl");
        $home = get_option("home");
        $content = get_post_field("post_content",$post_id);
        $author_id = get_post_field("post_author",$post_id);
        $img = preg_match_all("/<img.*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png|\.bmp]))[\'|\"].*?[\/]?>/i", $content, $img_url);
        if( count($img_url)>1 ){
            foreach($img_url[1] as $key=>$img_src){
                if(strpos($img_src,$siteurl)!==false || strpos($img_src,$home)!==false){
                } else {
                    $remote      = wp_remote_get( $img_src );
                    if ( !is_wp_error( $remote ) ){						
                        $file        = $remote["body"];
                        $mime_type   = $remote["headers"]["content-type"];
                        $file_name   = preg_replace("/.+\/(.+)\.(jpg|png|gif|bmp)$/i","$1.$2",$img_src);
                        $file_title  = preg_replace("/.+\/(.+)\.(jpg|png|gif|bmp)$/i","$1",$img_src);
                        $file_result = wp_upload_bits($file_name,'',$file);
                        $content     = str_replace($img_src,$file_result['url'],$content);
                        $attachment  = array(
                            'guid'           => $file_result['url'],
                            'post_mime_type' => $mime_type,
                            'post_title'     => $file_title,
                            'post_content'   => '',
                            'post_author'    => $author_id,
                            'post_status'    => 'inherit'
                        );
                        $attach_id   = wp_insert_attachment($attachment,$file_result['file'],$post_id);
                        $attach_data = wp_generate_attachment_metadata($attach_id,$file_result['file']);
                        wp_update_attachment_metadata($attach_id,$attach_data);
                    }
                }
            }        
        }
        $content = preg_replace("/href=([\'|\"]).*([\'|\"])[\/]?>/i","href=$1".$siteurl."$2>",$content);
        $wpdb->update( 
            $wpdb->posts, 
            array( 
                'post_content' => $content
            ), 
            array( 'ID' => $post_id ), 
            array( 
                '%s',
            ), 
            array( '%d' ) 
        );
    }
}
add_action( 'save_post', 'save_remote_image' );
endif;


if (!function_exists('bls_buffer_replace')):
/*
 * 输出时替换掉部分内容
**/
function bls_buffer_replace($buffer) {
    $locale = get_locale();
    if($locale=="zh_CN"){
        //大陆不推荐使用谷歌字体
        $buffer = preg_replace("/<link(.+)googleapis(.+)\/>\n/", "", $buffer);
    }
    if(is_admin()){
        $buffer = str_replace('WordPress', get_bloginfo("name").'管理平台', $buffer);
        $buffer = str_replace('http://wordpress.org/', home_url(), $buffer);
    }
	return $buffer;
}
endif;


if (!function_exists('bls_buffer_start')):
function bls_buffer_start() {
	@ob_start("bls_buffer_replace");
}
add_action('init', 'bls_buffer_start', 100);
endif;


if (!function_exists('bls_buffer_end')):
function bls_buffer_end() {
	@ob_end_flush();
}
add_action('shutdown', 'bls_buffer_end');
endif;


if (!function_exists('bls_buffer_start')):
/*
 * 删除面板上无用的工具栏
**/
function bls_remove_dashboard_meta() {
    remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
    remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
    remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
    remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
    remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
    remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
    remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
    remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
    remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');//since 3.8
}
add_action( 'admin_init', 'bls_remove_dashboard_meta' );
endif;
?>

