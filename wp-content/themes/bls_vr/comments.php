<?php
if ( post_password_required() ) return;
?>
<div class="weui_panel weui_panel_access">
    <div class="weui_panel_bd">
        <?php
          wp_list_comments( 'type=comment&callback=bls_vr_comment' );
        ?>
    </div>
</div>