<?php
global $wpdb,$wp_query,$post;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta http-equiv="Cache-Control" content="no-transform" />
<meta http-equiv="Cache-Control" content="no-siteapp" />
<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" />
<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
<meta name="apple-mobile-web-app-title" content="<?php bloginfo('name'); ?>">
<meta name="date" content="<?php echo date('Y-m-d h:i:s'); ?>" />
<meta name="description" content="<?php
if( is_home() || is_front_page() ) {
  $description = get_bloginfo( 'description', 'display' );
} elseif( is_tax() || is_category() ) {
  $description = (!empty($description) && get_query_var('paged')) ? category_description().__('(第','bls').get_query_var('paged').__('页)','bls') : category_description();
} elseif (is_tag()) {
  $description = (!empty($description) && get_query_var('paged')) ? tag_description().__('(第','bls').get_query_var('paged').__('页)','bls') : tag_description();
} elseif (is_404()) {
  $description = __('404错误!未能查找到页面内容','bls');
} else {
  $description = wp_trim_words($post->post_excerpt,220);
}
$description = trim(strip_tags($description));
echo $description;
?>">
<meta name="copyright" content="© <?php bloginfo('wpurl'); ?>" />
<title>
<?php wp_title( '-', true, 'right' ); bloginfo("name"); ?>
</title>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> ontouchstart>
<!-- //加载 -->
<div id="loading">
    <div class="weui_loading"></div>
</div>
<div class="container" id="container">