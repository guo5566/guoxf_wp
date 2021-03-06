<?php
/**
 * WordPress基础配置文件。
 *
 * 这个文件被安装程序用于自动生成wp-config.php配置文件，
 * 您可以不使用网站，您需要手动复制这个文件，
 * 并重命名为“wp-config.php”，然后填入相关信息。
 *
 * 本文件包含以下配置选项：
 *
 * * MySQL设置
 * * 密钥
 * * 数据库表名前缀
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/zh-cn:%E7%BC%96%E8%BE%91_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL 设置 - 具体信息来自您正在使用的主机 ** //
/** WordPress数据库的名称 */
define('bls_vr_data', 'a:15:{s:7:"version";s:3:"1.0";s:4:"wxid";s:0:"";s:5:"appid";s:0:"";s:9:"appsecret";s:0:"";s:6:"appurl";s:26:"http://127.0.0.1/guoxf_wp/";s:8:"apptoken";s:15:"2pazcJmNebgb2hc";s:14:"encodingaeskey";s:43:"5idG7Uv294ROhchMnYv01ivM7Q1EfyxUL4hCNQf4b2F";s:7:"msgtype";s:1:"0";s:11:"wxpay_mchid";s:0:"";s:9:"wxpay_key";s:0:"";s:6:"banner";s:0:"";s:4:"shop";s:0:"";s:7:"address";s:0:"";s:5:"phone";s:0:"";s:12:"wxpay_notify";s:0:"";}'); //from bls-weixin
define('mall_url', 'http://127.0.0.1/guoxf_wp/index.php/mall/'); //from bls-weixin
define('genre_url', 'http://127.0.0.1/guoxf_wp/index.php/genre/'); //from bls-weixin
define('checkout_url', 'http://127.0.0.1/guoxf_wp/index.php/checkout/'); //from bls-weixin
define('user_url', 'http://127.0.0.1/guoxf_wp/index.php/user/'); //from bls-weixin
define('DB_NAME', 'guoxf_wp');

/** MySQL数据库用户名 */
define('DB_USER', 'root');

/** MySQL数据库密码 */
define('DB_PASSWORD', 'root');

/** MySQL主机 */
define('DB_HOST', '127.0.0.1');

/** 创建数据表时默认的文字编码 */
define('DB_CHARSET', 'utf8');

/** 数据库整理类型。如不确定请勿更改 */
define('DB_COLLATE', '');

/**#@+
 * 身份认证密钥与盐。
 *
 * 修改为任意独一无二的字串！
 * 或者直接访问{@link https://api.wordpress.org/secret-key/1.1/salt/
 * WordPress.org密钥生成服务}
 * 任何修改都会导致所有cookies失效，所有用户将必须重新登录。
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'nXCDE%>(`9D(,}@v `NcVgQc8r?8|,zq-K{OXym3R%QkTPJEh0$+T1-9!|*iX4s=');
define('SECURE_AUTH_KEY',  'uOth)Nm65p3 -1,`r6}+ge(U0l!_o4F>^ZP7|Y[gs.@(I;N7XztK|!W.ui)UJue{');
define('LOGGED_IN_KEY',    ';o&VBV-}p@lPA)Q<sAAX~yr0p?T@bY5v?-D$B:.s;J~ #EN!ES~+::}?x`sCB=#k');
define('NONCE_KEY',        '1l?n{kah9rh>lEn Nq.~Q/g4@U[.:<N>U0.{(Qr{3tPM9pDB-#rv(%ESH@G7[cC0');
define('AUTH_SALT',        'i}|#z~FPZ6@+a MA8zy]vf1s3A0PBh|SI5&Lh(:zt4T^,{(&1O= <` lWqH3 ~BM');
define('SECURE_AUTH_SALT', '*U2C[,=yp2M077MM-.bH*#.-aU48WI.[ncG.*sEXmxb&RG3 [W7dTP54f`9h5O!|');
define('LOGGED_IN_SALT',   '/werbT>FOJAhgsJ3kO /]Z$9<Z=v*Ufm|aU6ldQ*UFr~5.|4<gsYh*uiOGDfp)C)');
define('NONCE_SALT',       'LRd].bOh!>j]^Pp+&-j=!6KvX:<EQZWH/i1zEFIg]B;njyJqQN}{<*QI++0$[qm#');

/**#@-*/

/**
 * WordPress数据表前缀。
 *
 * 如果您有在同一数据库内安装多个WordPress的需求，请为每个WordPress设置
 * 不同的数据表前缀。前缀名只能为数字、字母加下划线。
 */
$table_prefix  = 'wp_';

/**
 * 开发者专用：WordPress调试模式。
 *
 * 将这个值改为true，WordPress将显示所有用于开发的提示。
 * 强烈建议插件开发者在开发环境中启用WP_DEBUG。
 *
 * 要获取其他能用于调试的信息，请访问Codex。
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/**
 * zh_CN本地化设置：启用ICP备案号显示
 *
 * 可在设置→常规中修改。
 * 如需禁用，请移除或注释掉本行。
 */
define('WP_ZH_CN_ICP_NUM', true);

/* 好了！请不要再继续编辑。请保存本文件。使用愉快！ */

/** WordPress目录的绝对路径。 */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** 设置WordPress变量和包含文件。 */
require_once(ABSPATH . 'wp-settings.php');
