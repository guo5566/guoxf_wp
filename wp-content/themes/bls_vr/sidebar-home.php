<?php
global $wp_query,$wpdb;
$banner = get_bls_vr_option("banner");
if($banner){
add_action("wp_footer","bls_vr_footer_scripts_plus",100);
function bls_vr_footer_scripts_plus(){
?>
<script>
jQuery(document).ready(function($) {
    var swiper = new Swiper($('#hot-banner'),{
        pagination: $('#hot-banner .swiper-pagination'),
        loop: true,
        autoplay : 8000,
        autoHeight: true,
        autoplayDisableOnInteraction : false
    });
});
</script>
<?php
}
$banner_ids = preg_split("/\D/", $banner);
?>
    <div class="swiper-container" id="hot-banner">
        <div class="swiper-wrapper">
<?php
    foreach($banner_ids as $key => $aid){
        $img_url = wp_get_attachment_image_src($aid,"full");
        $post = $wpdb->get_row("select * from {$wpdb->posts} where post_content like '%wp-image-{$aid}%' and post_status='publish' order by ID desc limit 0,1");
        if($post){
?>
            <div class="swiper-slide"><a href="<?php echo get_permalink($post->ID); ?>"><img src="<?php echo $img_url[0]; ?>" alt="<?php echo $post->post_title; ?>"> <span><?php echo $post->post_title; ?></span></a></div>
<?php
        } else {
?>
            <div class="swiper-slide"><img src="<?php echo $img_url[0]; ?>" alt=""></div>
<?php
        }
    }
?>
        </div>
        <div class="swiper-pagination swiper-pagination-white"></div>
    </div>
<?php
}
?>
    <div class="weui_panel weui_panel_access">
        <div class="weui_panel_bd">
        <?php
        if ( have_posts() ) :
            while ( have_posts() ) : the_post();
        ?>
            <a href="<?php the_permalink(); ?>" class="weui_media_box weui_media_appmsg">
                <div class="weui_media_hd"> <img class="weui_media_appmsg_thumb" src="<?php bls_thumbnail(); ?>" alt=""> </div>
                <div class="weui_media_bd">
                    <h4 class="weui_media_title"><?php echo wp_trim_words($post->post_title, 26, "..."); ?></h4>
                    <p class="weui_media_desc"><?php echo get_the_date("Y-m-d"); ?> by <?php the_author(); ?></p>
                </div>
            </a>
        <?php
            endwhile;
        endif;
        ?>
        </div>
    </div>
    <?php
    $page_num = isset($wp_query->query_vars["paged"]) ? max(1,intval($wp_query->query_vars["paged"])) : 1;
    if ( $page_num < $wp_query->max_num_pages){
        $next = min(($page_num+1), $wp_query->max_num_pages);
    ?>
    <div class="weui_btn_area">
        <a class="weui_btn weui_btn_default" href="<?php echo get_pagenum_link($next); ?>" role="loadpage" data-wrap=".weui_panel_bd">更多</a>
    </div>
    <?php
    }
    get_sidebar();






    