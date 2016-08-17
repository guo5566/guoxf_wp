<?php
global $wpdb;
$malls = get_terms("album", array(
    'hide_empty' => true,
));
if($malls){
?>
<div class="mall_mode">
    <div class="weui_panel weui_panel_access">
    <?php
        foreach($malls as $mall){
    ?>
        <div class="weui_panel_hd"><?php echo $mall->name; ?><small><?php echo $mall->description; ?></small></div>
    <?php
            //先获取全部
            $page = get_query_var( 'page' ) ? get_query_var( 'page' ) : 1;
            $args = array(
                'post_type' => 'goods',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'album',
                        'field'    => 'slug',
                        'terms'    => $mall->slug
                    )
                )
            );
            $the_query = new WP_Query( $args );
            if ( $the_query->have_posts() ) {
    ?>
        <div class="weui_panel_bd game_mode">
    <?php
                while ( $the_query->have_posts() ) : $the_query->the_post();
    ?>
            <a href="<?php the_permalink(); ?>" class="weui_media_box weui_media_appmsg">
              <div class="weui_media_hd"> <img class="weui_media_appmsg_thumb" src="<?php bls_thumbnail(); ?>" alt=""> </div>
              <div class="weui_media_bd">
                  <h4 class="weui_media_title"><?php the_title(); ?></h4>
                  <p class="weui_media_desc">热度：<?php
                      $level = max(0,min(5,get_post_meta(get_the_ID(), "level", true)));
                      for($i=0;$i<$level;$i++) echo '<i class="weui_icon_fire"></i>';
                  ?></p>
                  <p class="weui_media_desc"><?php
                      $game = get_post_meta(get_the_ID(), "game", true);
                      echo count($game);
                  ?>款游戏 大约<?php
                      $duration = get_post_meta(get_the_ID(), "duration", true);
                      echo round($duration,2);
                  ?>分钟</p>
              </div>
            </a>
            <div class="weui_cell ablum_info">
            <div class="weui_cell_bd weui_cell_primary">
              <p>优惠价：<big><em><?php echo get_post_meta(get_the_ID(), "cost", true); ?></em>元</big> <del>原价：<?php echo get_post_meta(get_the_ID(), "price", true); ?></del></p>
            </div>
            <div class="weui_cell_bd weui_rtl">
              <a href="<?php echo add_query_arg("sku", get_the_ID(), checkout_url); ?>" class="weui_btn weui_btn_mini weui_btn_primary ">预定</a>
            </div>
            </div>
    <?php
                endwhile;
    ?>
        </div>
    <?php
            }
    ?>
        <a class="weui_panel_ft" href="<?php echo get_term_link($mall->term_id); ?>">查看更多</a>
    <?php
        }
    ?>
    </div>
</div>
<?php
} else {
?>
<div class="weui_msg"><br>
    <br>
    <div class="weui_icon_area"><i class="weui_icon_msg weui_icon_nothing"></i></div>
    <div class="weui_text_area">
        <p class="weui_msg_desc">没有纪录，请返回订购页</p>
    </div>
</div>
<?php
}
get_sidebar();






    

