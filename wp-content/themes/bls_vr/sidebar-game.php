<?php
global $post,$current_user,$wpdb;
if(isset($_POST["comment"])){
    $time = current_time('mysql');
    if(isset($current_user->ID) && $current_user->ID>0){
        $user_id = $current_user->ID;
        $user_info =  bls_weixin_user_meta($user_id);
        $data = array(
            'comment_post_ID' => $post->ID,
            'comment_author' => $user_info->display_name,
            'comment_content' => wp_kses($_POST["comment"], ""),
            'user_id' => $user_id,
            'comment_date' => $time,
            'comment_approved' => 1,
        );
    } else {
        $data = array(
            'comment_post_ID' => $post->ID,
            'comment_author' => "匿名",
            'comment_content' => wp_kses($_POST["comment"], ""),
            'comment_date' => $time,
            'comment_approved' => 0,
        );
    }
    wp_insert_comment($data);
    wp_redirect(get_permalink());
}
add_action("wp_footer","bls_vr_footer_scripts_plus",100);
function bls_vr_footer_scripts_plus(){
?>

<script>
jQuery(document).ready(function($) {
    function switch_navbar(i){
        $(".game_tab>.weui_navbar>.weui_navbar_item").eq(i).addClass("weui_bar_item_on").siblings().removeClass("weui_bar_item_on");
        $(".game_tab>.weui_tab_bd>div").eq(i).show().siblings().hide();
    }
    $(".game_tab>.weui_navbar>.weui_navbar_item").click(function(){
        var i=$(this).index();
        switch_navbar(i);
        switch(i){
            default:
                $("#comment-form").removeClass("weui_bar_item_on").siblings().addClass("weui_bar_item_on");
            break;
            case 1:
                $("#comment-form").addClass("weui_bar_item_on").siblings().removeClass("weui_bar_item_on");
            break;
            case 2:
                $("#comment-form,#comment-btn,#checkout-btn").removeClass("weui_bar_item_on");
            break;
        }
    });
    $("#comment-btn").click(function(){
        switch_navbar(1);
        $("#comment-form").addClass("weui_bar_item_on").siblings().removeClass("weui_bar_item_on");
    });
    $("#checkout-btn").click(function(){
        switch_navbar(2);
        $("#comment-form,#comment-btn,#checkout-btn").removeClass("weui_bar_item_on");
    });
    switch_navbar(0);
});
</script>
<?php
}
?>
    <div class="tabbar">
        <div class="weui_tab">
            <div class="weui_tab_bd">
                <div class="weui_game">
                    <div class="weui_cells weui_cells_form game_mode">
                        <a href="<?php echo get_post_meta(get_the_ID(), "mp4", true); ?>" target="_blank" class="weui_media_mp4" id="mp4-wrap" style="background-image: url(<?php bls_thumbnail(get_the_ID()); ?>); background-size: 100% auto; background-position: center top;">
                            <!--<video id="mp4" src="<?php echo get_post_meta(get_the_ID(), "mp4", true); ?>" class="weui_video"></video>
                            <script>
                            function mp4(){
                              var m=document.getElementById("mp4-wrap");v=document.getElementById("mp4");
                              if(v.paused ){
                                m.className = "weui_media_mp4 active";
                                v.play()
                              } else {
                                m.className = "weui_media_mp4";
                                v.pause();
                              }
                            }
                            </script>-->
                        </a>
                        <div class="weui_panel_bd" id="game_hd">
                            <div class="weui_media_box weui_media_appmsg">
                                <div class="weui_media_hd"> <img class="weui_media_appmsg_thumb" src="<?php bls_thumbnail(); ?>" alt=""> </div>
                                <div class="weui_media_bd">
                                    <h4 class="weui_media_title"><?php the_title(); ?></h4>
                                    <p class="weui_media_desc">热度：<?php
                                        $level = max(0,min(5,get_post_meta(get_the_ID(), "level", true)));
                                        for($i=0;$i<$level;$i++) echo '<i class="weui_icon_fire"></i>';
                                    ?></p>
                                    <p class="weui_media_desc">类别：<?php
                                        $terms = get_the_terms(get_the_ID(), "genre");
                                        if($terms){
                                            foreach($terms as $term){
                                               echo $term->name." ";
                                            }
                                        }
                                    ?></p>
                                    <p class="weui_media_desc">建议时长：<?php
                                        $duration = get_post_meta(get_the_ID(), "duration", true);
                                        echo round($duration,2);
                                    ?>分钟</p>
                                </div>
                            </div>
                            <div class="weui_media_box weui_media_appmsg">
                                <div class="weui_media_bd">
                                  <?php
                                  the_excerpt();
                                  ?>
                                </div>
                            </div>
                       </div>
                       <!-- 切换 -->
                       <div class="weui_tab game_tab">
                          <div class="weui_navbar">
                              <div class="weui_navbar_item">
                                  游戏介绍
                              </div>
                              <div class="weui_navbar_item">
                                  用户评论(<?php echo $post->comment_count; ?>)
                              </div>
                              <div class="weui_navbar_item weui_bar_item_on">
                                  相关套餐
                              </div>
                          </div>
                          <div class="weui_tab_bd">
                              <!-- 详情 -->
                              <div id="content_tab">
                              <?php
                              if($post->post_content){
                              ?>
                                  <div class="weui_article"><?php echo wpautop($post->post_content); ?></div>
                              <?php
                              } else{
                              ?>
                                  <div class="weui_msg">
                                      <div class="weui_icon_area"><i class="weui_icon_msg weui_icon_info"></i></div>
                                      <div class="weui_text_area">
                                          <p class="weui_msg_desc">没有纪录</p>
                                      </div>
                                  </div>
                              <?php
                              }
                              ?>
                              </div>
                              <!-- 评论 -->
                              <div id="comment_tab">
                              <?php
                              $album = get_post_meta(get_the_ID(), "album", true);
                              if($post->comment_count>0){;
                              ?>
                                  <?php comments_template(); ?>
                              <?php
                              } else{
                              ?>
                                  <div class="weui_msg">
                                      <div class="weui_icon_area"><i class="weui_icon_msg weui_icon_info"></i></div>
                                      <div class="weui_text_area">
                                          <p class="weui_msg_desc">没有纪录</p>
                                      </div>
                                  </div>
                              <?php
                              }
                              ?>
                              </div>
                              <!-- 套餐 -->
                              <div id="album_tab">
                              <?php
                              $album = get_post_meta(get_the_ID(), "album", true);
                              if(is_array($album)){
                                  $the_query = new WP_Query( array( 'post_type' => 'goods', 'post__in' => $album ) );
                              ?>
                                  <div class="weui_panel weui_panel_access">
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
                                  </div>
                              <?php
                              } else{
                              ?>
                                  <div class="weui_msg">
                                      <div class="weui_icon_area"><i class="weui_icon_msg weui_icon_info"></i></div>
                                      <div class="weui_text_area">
                                          <p class="weui_msg_desc">没有纪录</p>
                                      </div>
                                  </div>
                              <?php
                              }
                              ?>
                              </div>
                          </div>
                      </div>
                    </div>
                    <?php get_sidebar(); ?>
                </div>
            </div>
            <div class="weui_tabbar">
                <form method="post" class="weui_tabbar_item weui_comment_form" id="comment-form">
                    <input class="weui_comment_form_input" autocomplete="off" type="text" name="comment" placeholder="添加评论">
                    <button type="submit" class="weui_btn weui_btn_primary weui_comment_form_submit">发送</button>
                </form>
                <button class="weui_tabbar_item weui_comment weui_bar_item_on" id="comment-btn">
                  <i class="weui_icon_comment"></i> 评论
                </button>
                <button class="weui_tabbar_item weui_btn weui_btn_primary weui_bar_item_on" id="checkout-btn">立即预定</button>
            </div>
        </div>
    </div>
<script>