<?php
        global $wpdb;
        $genres = get_terms("genre", array(
            'hide_empty' => false,
        ));
        $term_ids = isset($_GET["genre"]) ? preg_split("/\D/", $_GET["genre"]) : array();
?>
    <div class="weui_filter on" id="filter">
        <span class="weui_filter_switch" onclick="switch_on();"><i class="weui_icon_down"></i><i class="weui_icon_up"></i></span>
        <?php
        foreach($genres as $genre){
            $class = "";
            $genre_ids = $term_ids;
            if(in_array($genre->term_id, $genre_ids)){
                $class = ' class="active"';
                foreach($genre_ids as $key=>$val){
                    if($val == $genre->term_id){
                        unset($genre_ids[$key]);
                    }
                }
            } else {
                $genre_ids[] = $genre->term_id;
            }
            $url = count($genre_ids)>0 ? add_query_arg("genre", implode("-", $genre_ids), genre_url) : genre_url;
        ?>
        <a href="<?php echo $url; ?>" <?php echo $class; ?>><?php echo $genre->name; ?></a>
        <?php
        }
        ?>
    </div>
<script>
function switch_on(){
    var f = document.getElementById("filter"),c = f.className;
    if(c.indexOf("on")>0){
       f.className = c.replace("on", "");
    } else {
       f.className = c+" on";
    }
}
</script>
        <?php
        //先获取全部
	global $wp_query;
        $page = max(1,$wp_query->query_vars["paged"]);
        $args = array(
            'paged' => $page,
            'post_type' => 'game'
        );
        if(count($term_ids)>0){
            $args['tax_query'] = array(
                'relation' => 'AND'
            );
            foreach($term_ids as $key=>$term_id){
                $args['tax_query'][] = array(
                    'taxonomy' => 'genre',
                    'field'    => 'term_id',
                    'terms'    => $term_id,
                );
            }
        }
        $the_query = new WP_Query( $args );
        if ( $the_query->have_posts() ) {
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
            </a>
        <?php
            endwhile;
        ?>
        </div>
    </div>
        <?php
    $page_num = isset($the_query->query_vars["paged"]) ? max(1,intval($the_query->query_vars["paged"])) : 1;
    if ( $page_num < $the_query->max_num_pages){
        $next = min(($page_num+1), $the_query->max_num_pages);
    ?>
    <div class="weui_btn_area">
        <a class="weui_btn weui_btn_default" href="<?php echo get_pagenum_link($next); ?>" role="loadpage" data-wrap=".weui_panel_bd">更多</a>
    </div>
    <?php
    }
        } else {
        ?>
    <div class="weui_msg"><br><br>
        <div class="weui_icon_area"><i class="weui_icon_msg weui_icon_nothing"></i></div>
        <div class="weui_text_area">
            <p class="weui_msg_desc">没有纪录，请返回订购页</p>
        </div>
    </div>
        <?php
        }
        get_sidebar();






    