<?php
global $wp_query;
if(have_posts()){
?>
<div class="mall_mode">
    <div class="weui_panel weui_panel_access">
        <div class="weui_panel_hd"><?php single_tag_title(); ?><small><?php echo tag_description(); ?></small></div>
        <div class="weui_panel_bd game_mode">
    <?php
                while ( have_posts() ) : the_post();
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
              <a href="<?php the_permalink(); ?>" class="weui_btn weui_btn_mini weui_btn_primary ">预定</a>
            </div>
            </div>
    <?php
                endwhile;
    ?>
        </div>
    <?php
                $page_num = isset($wp_query->query_vars["paged"]) ? max(1,intval($wp_query->query_vars["paged"])) : 1;
                if ( $page_num < $wp_query->max_num_pages){
                    $next = min(($page_num+1), $wp_query->max_num_pages);
    ?>
        <a class="weui_panel_ft" href="<?php echo get_pagenum_link($next); ?>" role="loadpage" data-wrap=".weui_panel_bd">查看更多</a>
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
        <p class="weui_msg_desc">没有纪录</p>
    </div>
</div>
<?php
}
get_sidebar();






    

