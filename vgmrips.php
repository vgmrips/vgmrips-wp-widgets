<?php

/* Plugin Name: VGMRips Widgets */

class VGMRipsLatestPacksWidget extends WP_Widget {
	public $sort = 'latest';

	public function __construct() {
		$widget_ops = array(
			'classname' => 'vgmrips_'.$this->sort.'_packs',
			'description' => 'Lists '.$this->sort.' packs on vgmrips',
		);
		parent::__construct( 'vgmrips_'.$this->sort.'_packs_widget', 'VGMRips '.ucfirst($this->sort).' Packs', $widget_ops);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		?>
		<ul id="vgmrips-<?php echo $this->sort ?>-packs">
<?php
		$result = json_decode(get_option('vgmrips_'.$this->sort.'_packs'), true);
		foreach($result['packs'] as $pack) {
			$sysNames = array();
			foreach($pack['systems'] as $sys) $sysNames[] = $sys['short_name'] ? $sys['short_name'] : ($sys['clean_name'] ? $sys['clean_name'] : $sys['name']);
			$chipNames = array();
			foreach($pack['chips'] as $chip) $chipNames[] = $chip['short_name'] ? $chip['short_name'] : $chip['name'];
			?>
			<li>
				<a href="http://vgmrips.net/packs/pack/<?php echo $pack['url']; ?>" title="<?php echo htmlspecialchars($pack['title']) ?>" target="_blank" style="display: block; white-space: nowrap; text-overflow: ellipsis; overflow: hidden">
					<img style="float: left; width: 50px; height: 50px; margin-right: 8px; margin-bottom: 8px" src="http://vgmrips.net/packs/images/tiny/<?php echo htmlspecialchars($pack['image']) ?>" alt="">
					<?php echo htmlspecialchars($pack['title']) ?>
					<br />
					<small>
					<?php echo implode(', ', array_map('htmlspecialchars', $sysNames)); ?>
					&bull;
					<?php echo implode(', ', array_map('htmlspecialchars', $chipNames)); ?>
					</small>
					<div style="clear: both"></div>
				</a>
			</li>
<?php
		}
?>
		</ul>
		<div style="text-align: right"><a href="http://vgmrips.net/packs/<?php echo $this->sort ?>" target="_blank">See all &raquo;</a></div>
		<?php
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( ucfirst($this->sort).' Packs from VGMRips', 'text_domain' );
		$num_packs = ! empty( $instance['num_packs'] ) ? $instance['num_packs'] : __( '5', 'text_domain' );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
};

class VGMRipsTopPacksWidget extends VGMRipsLatestPacksWidget {
	public $sort = 'top';
};

add_action('widgets_init', 'vgmrips_widgets_init');
function vgmrips_widgets_init() {
	register_widget('VGMRipsLatestPacksWidget');
	register_widget('VGMRipsTopPacksWidget');
}

if(!wp_next_scheduled('vgmrips_get_packs_hook')) {
	wp_schedule_event(time(), 'hourly', 'vgmrips_get_packs_hook');
}

add_action('vgmrips_get_packs_hook', 'vgmrips_get_packs');

function vgmrips_get_packs() {
	update_option('vgmrips_latest_packs', file_get_contents('http://vgmrips.net/packs/json/latest.json?limit=5'));
	update_option('vgmrips_top_packs', file_get_contents('http://vgmrips.net/packs/json/top.json?limit=5'));
}
