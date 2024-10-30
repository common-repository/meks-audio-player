<div class="meks-ap meks-ap-bg">

	<a class="meks-ap-toggle" href="javascript:void(0);">
		<span class="meks-ap-collapse-text"><i class="apf apf-minimize"></i></span>
		<span class="meks-ap-show-text"><i class="apf apf-maximize"></i></span>
	</a>

	<?php do_action('meks_ap_player_before'); //theme can hook here ?>
	<?php $current_id = is_singular() ? get_the_ID() : 0; ?>
	<div id="meks-ap-player" class="meks-ap-player" data-playing-id="<?php echo esc_attr($current_id); ?>">
		
	</div>

	<?php do_action('meks_ap_player_after'); //theme can hook here ?>
  
</div>