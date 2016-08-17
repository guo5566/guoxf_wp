<?
/*
Plugin Name: Javascript QRCode Generator 
Plugin URI: http://99webtools.com/qrcode.php
Description: Pure javascript based qrcode generator
Version: 1.0
Author: Sunny Verma
Author URI: http://99webtools.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/
add_action('wp_enqueue_scripts','js_qrcode_script');
add_shortcode('jsqr','js_qrcode');
function js_qrcode_script(){
wp_enqueue_script( 'jsqrcode-js', plugins_url( 'qrcode.js' , __FILE__ ));
}
function js_qrcode($attr,$cont=null){
extract(shortcode_atts( array(
		'msg' => "Javascript QRcode Generator",
		'size' => 150,
		'ecc' => "H"
	 	), $attr ));
		$r=rand(0,9999);
return '<div id="jsqr'.$r.'"></div>
<script type="text/javascript">
new QRCode(document.getElementById("jsqr'.$r.'"), {
	text: "'.$msg.'",
	width: '.$size.',
	height: '.$size.',
	colorDark : "#000000",
	colorLight : "#ffffff",
	correctLevel : QRCode.CorrectLevel.'.$ecc.'
});
</script>';
}
add_action( 'init', 'jsqr_buttons' );
function jsqr_buttons() {
    add_filter( "mce_external_plugins", "jsqr_add_buttons" );
    add_filter( 'mce_buttons', 'jsqr_register_buttons' );
}
function jsqr_add_buttons( $plugin_array ) {
    $plugin_array['jsqr'] = plugins_url( 'custom/jsqr-plugin.js' , __FILE__ );
    return $plugin_array;
}
function jsqr_register_buttons( $buttons ) {
    array_push( $buttons,'jsqrcode_button' );
    return $buttons;
}

//jsqr widget
class jsqr_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'jsqr_widget', // Base ID
			'QRcode Widget', // Name
			array( 'description' => __( 'QR code widget', 'text_domain' ), ) // Args
		);
	}

	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$text = $instance[ 'text' ];
		$size = $instance[ 'size' ];
		$ecc = $instance[ 'ecc' ];
		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		echo '<div id="jsqrw"></div>
<script type="text/javascript">
new QRCode(document.getElementById("jsqrw"), {
	text: "'.$text.'",
	width: '.$size.',
	height: '.$size.',
	colorDark : "#000000",
	colorLight : "#ffffff",
	correctLevel : QRCode.CorrectLevel.'.$ecc.'
});
</script>';
		echo $after_widget;
	}

 	public function form( $instance ) {
		$text = isset($instance[ 'text' ])?$instance[ 'text' ]:"Enter TEXT";
		$size = isset($instance[ 'size' ])?$instance[ 'size' ]:"150";
		$title = isset($instance[ 'title' ])?$instance[ 'title' ]:"My QR Widget";
		$ecc = $instance[ 'ecc' ];
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		<label for="<?php echo $this->get_field_id( 'text' ); ?>"><?php _e( 'TEXT:' ); ?></label> 
		<textarea class="widefat" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>" ><?php echo esc_attr( $text ); ?></textarea>
		<label for="<?php echo $this->get_field_id( 'size' ); ?>"><?php _e( 'SIZE:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>" type="text" value="<?php echo esc_attr( $size ); ?>" />
		<label for="<?php echo $this->get_field_id( 'ecc' ); ?>"><?php _e( 'ECC:' ); ?></label> 
		<select class="widefat" id="<?php echo $this->get_field_id( 'ecc' ); ?>" name="<?php echo $this->get_field_name( 'ecc' ); ?>">
	<option value="L">L</option>
	<option value="M">M</option>
	<option value="H" selected="selected">H</option>
	<option value="Q">Q</option>
	</select>
		</p>
		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['text'] = strip_tags( $new_instance['text'] );
		$instance['size'] = strip_tags( $new_instance['size'] );
		$instance['ecc'] = strip_tags( $new_instance['ecc'] );
		return $instance;
	}

}
add_action( 'widgets_init', create_function( '', 'register_widget( "jsqr_widget" );' ) );
?>