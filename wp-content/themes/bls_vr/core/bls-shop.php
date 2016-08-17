<?php
/**
 * 商城 * 核心
 * ver 1.0 core
**/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 创建游戏模型
**/
add_action( 'init', 'bls_create_shop_init' );
function bls_create_shop_init() {
    /**
     * 添加游戏分类
     */
	$args = array(
		'hierarchical'      => true,
		'labels'            => array(
		'name'              => __( '游戏分类', 'bls-vr' ),
		'singular_name'     => __( '游戏分类', 'bls-vr' ),
		'search_items'      => __( '搜索游戏', 'bls-vr' ),
		'all_items'         => __( '所有的游戏', 'bls-vr' ),
		'parent_item'       => __( '所属游戏分类', 'bls-vr' ),
		'parent_item_colon' => __( '所属游戏分类:', 'bls-vr' ),
		'edit_item'         => __( '编辑游戏分类', 'bls-vr' ),
		'update_item'       => __( '更新游戏分类', 'bls-vr' ),
		'add_new_item'      => __( '添加游戏分类', 'bls-vr' ),
		'new_item_name'     => __( '新的游戏分类', 'bls-vr' ),
		'menu_name'         => __( '游戏分类', 'bls-vr' )),
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'genre' ),
	);
	register_taxonomy( 'genre', array( 'genre' ), $args );

    /**
     * 添加套餐主分类
     */
	$args = array(
		'hierarchical'      => true,
		'labels'            => array(
		'name'              => __( '套餐分类', 'bls-vr' ),
		'singular_name'     => __( '套餐分类', 'bls-vr' ),
		'search_items'      => __( '搜索套餐', 'bls-vr' ),
		'all_items'         => __( '所有的套餐', 'bls-vr' ),
		'parent_item'       => __( '所属套餐分类', 'bls-vr' ),
		'parent_item_colon' => __( '所属套餐分类:', 'bls-vr' ),
		'edit_item'         => __( '编辑套餐分类', 'bls-vr' ),
		'update_item'       => __( '更新套餐分类', 'bls-vr' ),
		'add_new_item'      => __( '添加套餐分类', 'bls-vr' ),
		'new_item_name'     => __( '新的套餐分类', 'bls-vr' ),
		'menu_name'         => __( '套餐分类', 'bls-vr' )),
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'album' ),
	);
	register_taxonomy( 'album', array( 'album' ), $args );

    /**
     * 添加游戏类型
     */
	$args = array(
		'labels'             => array(
		'name'               => __( '游戏', 'bls-vr' ),
		'singular_name'      => __( '游戏', 'bls-vr' ),
		'menu_name'          => __( '游戏', 'bls-vr' ),
		'name_admin_bar'     => __( '游戏', 'bls-vr' ),
		'add_new'            => __( '添加游戏', 'bls-vr' ),
		'add_new_item'       => __( '添加游戏', 'bls-vr' ),
		'new_item'           => __( '新游戏', 'bls-vr' ),
		'edit_item'          => __( '编辑游戏', 'bls-vr' ),
		'view_item'          => __( '查看游戏', 'bls-vr' ),
		'all_items'          => __( '所有的游戏', 'bls-vr' ),
		'search_items'       => __( '搜索游戏', 'bls-vr' ),
		'parent_item_colon'  => __( '属于游戏:', 'bls-vr' ),
		'not_found'          => __( '没有找到游戏.', 'bls-vr' ),
		'not_found_in_trash' => __( '没有找到游戏.', 'bls-vr' )),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'	 => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'game' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'supports'           => array('title','editor','thumbnail','excerpt','comments'),
    'menu_icon'          => 'dashicons-video-alt',
    'taxonomies'         => array('genre'),
    'register_meta_box_cb'=>'bls_create_game_metabox'
	);
	register_post_type( 'game', $args );

    /**
     * 添加套餐类型
     */
	$args = array(
		'labels'             => array(
		'name'               => __( '套餐', 'bls-vr' ),
		'singular_name'      => __( '套餐', 'bls-vr' ),
		'menu_name'          => __( '套餐', 'bls-vr' ),
		'name_admin_bar'     => __( '套餐', 'bls-vr' ),
		'add_new'            => __( '添加套餐', 'bls-vr' ),
		'add_new_item'       => __( '添加套餐', 'bls-vr' ),
		'new_item'           => __( '新套餐', 'bls-vr' ),
		'edit_item'          => __( '编辑套餐', 'bls-vr' ),
		'view_item'          => __( '查看套餐', 'bls-vr' ),
		'all_items'          => __( '所有的套餐', 'bls-vr' ),
		'search_items'       => __( '搜索套餐', 'bls-vr' ),
		'parent_item_colon'  => __( '属于套餐:', 'bls-vr' ),
		'not_found'          => __( '没有找到套餐.', 'bls-vr' ),
		'not_found_in_trash' => __( '没有找到套餐.', 'bls-vr' )),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'	 => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'goods' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'supports'           => array('title','editor','thumbnail'),
    'menu_icon'          => 'dashicons-cart',
    'taxonomies'         => array('album'),
    'register_meta_box_cb'=>'bls_create_goods_metabox'
	);
	register_post_type( 'goods', $args );
}

/**
 * 游戏显示附加字段数据
**/
add_filter( 'manage_game_posts_columns' , 'add_game_column' );
function display_game_custom_column( $column, $post_id ) {
    switch($column){
        case 'level':
            echo intval(get_post_meta($post_id,"level",true));
        break;
        case 'duration':
            echo round(get_post_meta($post_id,"duration",true), 2).'分钟';
        break;
    }
}

/**
 * 游戏显示附加字段
**/
add_action( 'manage_game_posts_custom_column' , 'display_game_custom_column', 10, 2 );
function add_game_column( $columns ) {
    $new_columns = array();
    $i = 0;
    foreach($columns as $key=>$val){
        if($i==2){
            $new_columns['level']       = '热度';
            $new_columns['duration']    = '时长';
        }
        $new_columns[$key] = $val;
        $i++;
    }        
    return $new_columns;
}

/**
 * 套餐显示附加字段数据
**/
add_filter( 'manage_goods_posts_columns' , 'add_goods_column' );
function display_goods_custom_column( $column, $post_id ) {
    switch($column){
        case 'goods_id':
            echo $post_id;
        break;
        case 'price':
            printf(__("&yen;%s元","bls-vr"), intval(get_post_meta($post_id,"price",true)));
        break;
        case 'cost':
            printf(__("&yen;%s元","bls-vr"), intval(get_post_meta($post_id,"cost",true)));
        break;
        case 'game':
            $game_ids = get_post_meta($post_id,"game",true);
            if(is_array($game_ids)){
                echo count($game_ids)."款游戏";
            } else {
                echo "0款游戏";
            }
        break;
        case 'level':
            echo intval(get_post_meta($post_id,"level",true));
        break;
        case 'duration':
            echo round(get_post_meta($post_id,"duration",true), 2).'分钟';
        break;
    }
}

/**
 * 套餐显示附加字段
**/
add_action( 'manage_goods_posts_custom_column' , 'display_goods_custom_column', 10, 2 );
function add_goods_column( $columns ) {
    $new_columns = array();
    $i = 0;
    foreach($columns as $key=>$val){
        if($i==2){
            $new_columns['goods_id']    = '套餐id';
            $new_columns['price']       = '原价';
            $new_columns['cost']        = '优惠价';
            $new_columns['game']        = '包含游戏';
            $new_columns['level']       = '热度';
            $new_columns['duration']    = '时长';
        }
        $new_columns[$key] = $val;
        $i++;
    }        
    return $new_columns;
}

/**
 * 游戏的附加属性设置
**/
function bls_create_game_metabox() {
    add_meta_box(
        'bls_base1',
        '套餐信息',
        'bls_render_game_side_metabox1',
        'game',
        'side',
        'high'
    );
    add_meta_box(
        'bls_base2',
        '特色视频',
        'bls_render_game_side_metabox2',
        'game',
        'side'
    );
}
/**
 * 游戏的附加属性配置栏
**/
function bls_render_game_side_metabox1($post){
    $attr = array (
        array(
            "title" => __( '热度', 'bls-vr' ),
            "desc" => __( '0-5之间的整数', 'bls-vr' ),
            "name" => "level",
            "val" => 5,
            "unit" => ""
        ),
        array(
            "title" => __( '时长', 'bls-vr' ),
            "desc" => __( '游戏的时间长度', 'bls-vr' ),
            "name" => "duration",
            "val" => 0,
            "unit" => "分钟"
        )
    );
    foreach($attr as $key=>$val){
        $name  = $val["name"];
        wp_nonce_field( $name, 'nonce_'.$name );
        $value = get_post_meta($post->ID,$name,true);
        $value = $value!="" ? $value : $val["val"];
        $unit  = $val["unit"];
?>
<p>
    <span><?php echo $val["title"]; ?>：</span>
    <input placeholder="<?php echo $val["desc"]; ?>" name="<?php echo $name; ?>" value="<?php echo $value; ?>" type="text" /><?php echo $unit; ?>
    <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $val["desc"]; ?>
</p>
<?php
    }
}
/**
 * 游戏的附加属性配置栏
**/
function bls_render_game_side_metabox2($post){
  wp_nonce_field( "mp4", 'nonce_mp4' );
  $mp4 = get_post_meta($post->ID,"mp4",true);
/*
  //if($mp4){
?>
<p class="hide-if-no-js" id="set-post-mp4">
  <video src="<?php echo $mp4; ?>" controls preload="true"></video>
</p>
<p class="hide-if-no-js howto" id="set-post-mp4-desc">点击视频来修改或更新</p>
<p class="hide-if-no-js" id="remove-post-mp4"><a href="javascript:;">删除特色视频</a></p>
<?php
  //} else {
?>
<p class="hide-if-no-js" id="set-post-mp4">
  <a href="javascript:;">设为特色视频</a>
</p>
<?php
  //}
*/
?>
<textarea rows="3" name="mp4" id="mp4"><?php echo esc_textarea($mp4); ?></textarea>
<?php /*
<input type="hidden" name="mp4" id="mp4" value="<?php esc_textarea($mp4); ?>">
<p class="hide-if-no-js"><a target="_blank" href="https://www.w3.org/2010/05/video/mediaevents.html">特色视频建议使用mp4格式</a></p>
<script>
jQuery(document).ready(function($) {
    var bls_upload_video_frame = wp.media({
        title: '请选择mp4视频',   
        button: {
            text: '确认选择',   
        },
        type: 'video',
        multiple: false   
    });
    $(document).on("click","#remove-post-mp4",function(e){
        $("#set-post-mp4-desc,#remove-post-mp4").remove();
        $("#set-post-mp4").html('<a href="javascript:;">设为特色视频</a>');
    }).on("click","#set-post-mp4",function(e){
        var t=$(this);
        bls_upload_video_frame.open().off('select').on('select',function(){
            attachment_data = bls_upload_video_frame.state().get('selection').first().toJSON();
            var extStart=attachment_data.url.lastIndexOf('.');
            var ext=attachment_data.url.substring(extStart,attachment_data.url.length).toUpperCase();
            if(ext=='.MP4'){
                $("#set-post-mp4").html('<video src="'+attachment_data.url+'" controls preload="true"></video>').after('<p class="hide-if-no-js howto" id="set-post-mp4-desc">点击视频来修改或更新</p><p class="hide-if-no-js" id="remove-post-mp4"><a href="javascript:;">删除特色视频</a></p>');
                $("#mp4").val(attachment_data.url);
            } else {
              alert("特色视频建议使用mp4格式!");
            }
        }); 
    });
});
</script>
<?php
*/
}

/**
 * 套餐的附加属性设置
**/
function bls_create_goods_metabox() {
    add_meta_box(
        'bls_base1',
        '套餐信息',
        'bls_render_goods_side_metabox1',
        'goods',
        'side',
        'high'
    );
    add_meta_box(
        'bls_base2',
        '扩展选项',
        'bls_render_goods_main_metabox2',
        'goods',
        'advanced',
        'high'
    );
}
/**
 * 套餐的附加属性配置栏
**/
function bls_render_goods_side_metabox1($post){
    $attr = array (
        array(
            "title" => __( '原价', 'bls-vr' ),
            "desc" => __( '游戏的市场价格', 'bls-vr' ),
            "name" => "price",
            "val" => 0,
            "unit" => "元"
        ),
        array(
            "title" => __( '售价', 'bls-vr' ),
            "desc" => __( '游戏的时间长度', 'bls-vr' ),
            "name" => "cost",
            "val" => 0,
            "unit" => "元"
        ),
        array(
            "title" => __( '热度', 'bls-vr' ),
            "desc" => __( '0-5之间的整数', 'bls-vr' ),
            "name" => "level",
            "val" => 5,
            "unit" => ""
        ),
        array(
            "title" => __( '时长', 'bls-vr' ),
            "desc" => __( '套餐包含的游戏时间总长度', 'bls-vr' ),
            "name" => "duration",
            "val" => 0,
            "unit" => "分钟"
        )
    );
    foreach($attr as $key=>$val){
        $name  = $val["name"];
        wp_nonce_field( $name, 'nonce_'.$name );
        $value = get_post_meta($post->ID,$name,true);
        $value = $value!="" ? $value : $val["val"];
        $unit  = $val["unit"];
?>
<p>
    <span><?php echo $val["title"]; ?>：</span>
    <input placeholder="<?php echo $val["desc"]; ?>" name="<?php echo $name; ?>" value="<?php echo $value; ?>" type="text" /><?php echo $unit; ?>
    <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $val["desc"]; ?>
</p>
<?php
    }
}
/**
 * 套餐的附加属性配置栏
**/
function bls_render_goods_main_metabox2($post){
    global $wpdb;
    $name = "game";
    wp_nonce_field( $name, 'nonce_'.$name );
    $results = $wpdb->get_results("select ID,post_title from $wpdb->posts where post_type='{$name}' and post_status='publish'");
    if(empty($results)){
        echo '<p><strong><span class="dashicons dashicons-video-alt"></span> 套餐包含的游戏 </strong>（<a href="'.admin_url("post-new.php?post_type=".$name).'" target="_blank">请先添加数据</a>）</p>';
    } else {
?>
<p style="border-bottom:1px dotted #eee;"><strong><span class="dashicons dashicons-video-alt"></span> 套餐包含的游戏 </strong></p>
<?php
        $value = (array)get_post_meta($post->ID,$name,true);
        foreach($results as $result){
        ?>
<label style="display:inline-block; border:1px solid #EEEEEE; padding:5px 8px; border-radius:3px;">
    <input name="<?php echo $name; ?>[]" type="checkbox" value="<?php echo $result->ID; ?>" <?php checked(in_array($result->ID,$value)); ?>/>
    <?php echo $result->post_title; ?> </label>
<?php
        }
    }
}

/**
 * 商城数据保存
**/
add_action( 'save_post', 'bls_save_goods_metabox' );
function bls_save_goods_metabox( $post_id ) {
    $post_type = get_post_type( $post_id );
    if(in_array($post_type, array("game","goods"))){
        $attr = $post_type == "game" ? array(
            "mp4",
            "level",
            "duration"
        ) : array(
            "price",
            "cost",
            "level",
            "duration",
            "game"
        );
        foreach($attr as $key => $name){
            if ( isset( $_POST[ 'nonce_'.$name ] ) && wp_verify_nonce( $_POST[ 'nonce_'.$name ], $name ) ){
                $value = $_POST[$name];
                update_post_meta( $post_id, $name, $value );
                if($name == "game"){
                    foreach($value as $num=>$pid){
                        $album = get_post_meta($pid, "album", true);
                        $album = is_array($album) ? $album : array();
                        if(!isset($album[$post_id])){
                            $album[$post_id] = $post_id;
                            update_post_meta($pid, "album", $album);
                        }
                    }
                }
            }
        }
    }
}

/**
 * Change code lost you hands
**/