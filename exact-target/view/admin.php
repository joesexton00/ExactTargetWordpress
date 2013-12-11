<?php
/**
 * Exact Target Admin Menu Page
 *
 * @author     Joe Sexton <joe.sexton@bigideas.com>
 * @package    WordPress
 * @subpackage exact-target
 */
?>

<style type="text/css">
	input:disabled { background:rgba(255, 13, 13, 0.23); }
</style>

<div class="wrap">

	<?php screen_icon( 'plugins' ); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<form action="options.php" method="post">
		<?php settings_fields( 'exact_target' ); ?>
		<?php do_settings_sections( "xt-admin"  ); ?>
		<?php submit_button(); ?>
	</form>

</div>



