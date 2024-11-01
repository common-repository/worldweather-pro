<?php
/*
	Plugin Name: WorldWeather Pro
	Description: Show the local weather on your website
	Author: Daniel J Griffiths
	Author URI: http://www.ghost1227.com
	Version: 1.0
	Text Domain: worldweather-pro
*/

// Localization fun
add_action('plugins_loaded', 'worldweather_pro_init');
function worldweather_pro_init() {
	load_plugin_textdomain('worldweather-pro', false, dirname(plugin_basename(__FILE__)) . '/lang/');
}

// Include CSS
add_action('wp_enqueue_scripts', 'worldweather_pro_css');
function worldweather_pro_css() {
	wp_register_style('worldweather-pro-css', plugins_url('css/style.css', __FILE__));
	wp_enqueue_style('worldweather-pro-css');
}

// Load the widget
add_action('widgets_init', 'worldweather_pro_widget');
function worldweather_pro_widget() {
	register_widget('WorldWeather_Pro_Widget');
}

// Main widget class
class WorldWeather_Pro_Widget extends WP_Widget {

	function WorldWeather_Pro_Widget() {
		$widget_ops		=	array('classname' => 'worldweather_pro', 'description' => __('Show the local weather on your website.', 'worldweather-pro'));
		$control_ops	=	array('id_base' => 'worldweather-pro', 'width' => 492);
		$this->WP_Widget('worldweather-pro', __('WorldWeather Pro', 'worldweather-pro'), $widget_ops, $control_ops);
	}

	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		$title			=	apply_filters('widget_title', $instance['title']);
		$apikey			=	$instance['apikey'];
		$location		=	$instance['location'];
		$days			=	$instance['days'];
		$tempunit		=	$instance['tempunit'];
		$windunit		=	$instance['windunit'];
		$search			=	$instance['search'];
		$lpadding		=	$instance['lpadding'];
		$rpadding		=	$instance['rpadding'];
		$disploc		=	$instance['disploc'];
		$dispicon		=	$instance['dispicon'];
		$dispdesc		=	$instance['dispdesc'];
		$disphighlow	=	$instance['disphighlow'];
		$dispwind		=	$instance['dispwind'];
		$disphumidity	=	$instance['disphumidity'];
		$disppressure	=	$instance['disppressure'];
		$dispcover		=	$instance['dispcover'];
		$dispvisibility	=	$instance['dispvisibility'];
		$disppop		=	$instance['disppop'];

		echo $before_widget;

		// Display widget title
		if($title)
			echo $before_title . $title . $after_title;

		if(!$apikey) {
			// World Weather Online API Key is required!
			echo sprintf(__('No World Weather Online API Key defined! If you don\'t have one, get one for free <a href="%s" target="_blank">here</a>!', 'worldweather-pro'), 'http://www.worldweatheronline.com/register.aspx');
		} else {
			// Get data from API
			$getweatherdata = curl_init();
			curl_setopt($getweatherdata, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($getweatherdata, CURLOPT_URL, 'http://free.worldweatheronline.com/feed/weather.ashx?q=' . ($location ? $location : 'Omaha') . '&format=json&num_of_days=' . ($days ? $days : '1') . '&key=' . $apikey);
			$weatherdata = curl_exec($getweatherdata);
			curl_close($getweatherdata);

			// Handle the data returned by the API
			$weatherdata = json_decode($weatherdata, true);

			if(!$weatherdata) {
				// Error if the curl command fails for some reason
				_e('Error: An unknown error occurred!', 'worldweather-pro');
			} elseif($weatherdata['data']['error']) {
				// Error if the API returns an error
				echo sprintf(__('Error: %s', 'worldweather-pro'), $weatherdata['data']['error']['0']['msg']);
			} else {
				// Display widget data
				if($disploc == 'enabled')
					echo '<h4 class="worldweather-location worldweather-centered">' . $location . '</h4>'; ?>

				<div class="worldweather-box">
					<div class="worldweather-col<?php echo ($dispicon == 'enabled' ? '-half"' . ($lpadding ? ' style="padding-top: ' . (is_numeric($lpadding) ? $lpadding . 'px' : $lpadding) . ';"' : '') : ''); ?>>
						<div class="worldweather-temp worldweather-centered"><?php echo ($tempunit == 'celsius' ? $weatherdata['data']['current_condition']['0']['temp_C'] . '&deg;C' : $weatherdata['data']['current_condition']['0']['temp_F'] . '&deg;F'); ?></div>
						<?php
							if($disphighlow == 'enabled')
								echo '<div class="worldweather-highlow worldweather-centered">' . ($tempunit == 'celsius' ? $weatherdata['data']['weather']['0']['tempMinC'] . '&deg;C / ' . $weatherdata['data']['weather']['0']['tempMaxC'] . '&deg;C' : $weatherdata['data']['weather']['0']['tempMinF'] . '&deg;F / ' . $weatherdata['data']['weather']['0']['tempMaxF'] . '&deg;F') . '</div>';
						?>
						<div class="worldweather-details">
							<div class="worldweather-col-third">
								<?php
									if($dispwind == 'enabled')
										echo '<div class="worldweather-sub-title worldweather-wind-title">' . __('Wind', 'worldweather-pro') . ':</div>';
									if($disphumidity == 'enabled')
										echo '<div class="worldweather-sub-title worldweather-humidity-title">' . __('Humidity', 'worldweather-pro') . ':</div>';
									if($disppressure == 'enabled')
										echo '<div class="worldweather-sub-title worldweather-pressure-title">' . __('Pressure', 'worldweather-pro') . ':</div>';
									if($dispcover == 'enabled')
										echo '<div class="worldweather-sub-title worldweather-cloud-cover-title">' . __('Cover', 'worldweather-pro') . ':</div>';
									if($dispvisibility == 'enabled')
										echo '<div class="worldweather-sub-title worldweather-visibility-title">' . __('Visibility', 'worldweather-pro') . ':</div>';
									if($disppop == 'enabled')
										echo '<div class="worldweather-sub-title worldweather-pop-title">' . __('P.O.P.', 'worldweather-pro') . ':</div>';
								?>
							</div>
							<div class="worldweather-col-two-third worldweather-right">
								<?php
									if($dispwind == 'enabled')
										echo '<div class="worldweather-sub-content worldweather-wind-content">' . ($windunit == 'kilometer' ? $weatherdata['data']['weather']['0']['windspeedKmph'] . 'kph / ' . $weatherdata['data']['weather']['0']['winddir16Point'] : $weatherdata['data']['weather']['0']['windspeedMiles'] . 'mph / ' . ' ' . $weatherdata['data']['weather']['0']['winddir16Point']) . '</div>';
									if($disphumidity == 'enabled')
										echo '<div class="worldweather-sub-content worldweather-humidity-content">' . $weatherdata['data']['current_condition']['0']['humidity'] . '%</div>';
									if($disppressure == 'enabled')
										echo '<div class="worldweather-sub-content worldweather-pressure-content">' . $weatherdata['data']['current_condition']['0']['pressure'] . ' mb</div>';
									if($dispcover == 'enabled')
										echo '<div class="worldweather-sub-content worldweather-cloud-cover-content">' . $weatherdata['data']['current_condition']['0']['cloudcover'] . '%</div>';
									if($dispvisibility == 'enabled')
										echo '<div class="worldweather-sub-content worldweather-visibility-content">' . ($windunit == 'kilometer' ? $weatherdata['data']['current_condition']['0']['visibility'] . 'km' : round(($weatherdata['data']['current_condition']['0']['visibility'] * 0.6214)) . 'mi') . '</div>';
									if($disppop == 'enabled')
										echo '<div class="worldweather-sub-content worldweather-pop-content">' . round($weatherdata['data']['current_condition']['0']['precipMM']) . '%</div>';
								?>
							</div>
							<div class="worldweather-clear"></div>
						</div>
					</div>
					<?php if($dispicon == 'enabled') {
						echo '<div class="worldweather-col-half"' . ($rpadding ? ' style="padding-top: ' . (is_numeric($rpadding) ? $rpadding . 'px' : $rpadding) . ';"' : '') . '>';
						echo '<img class="worldweather-icon" src="' . str_replace('http://www.worldweatheronline.com/images/wsymbols01_png_64/', plugins_url('img/', __FILE__), $weatherdata['data']['weather']['0']['weatherIconUrl']['0']['value']) . '" alt="' . $weatherdata['data']['weather']['0']['weatherDesc']['0']['value'] . '" title="' . $weatherdata['data']['weather']['0']['weatherDesc']['0']['value'] . '" />';
						if($dispdesc == 'enabled')
							echo '<div class="worldweather-description worldweather-centered">' . $weatherdata['data']['weather']['0']['weatherDesc']['0']['value'] . '</div>';
						echo '</div>';
					} ?>
				</div>
			<?php }
		}
		echo $after_widget;
	}

	// Update widget settings
	function update($new_instance, $old_instance) {
		$instance		=	$old_instance;

		$instance['title']			=	strip_tags($new_instance['title']);
		$instance['apikey']			=	strip_tags($new_instance['apikey']);
		$instance['location']		=	strip_tags($new_instance['location']);
		$instance['days']			=	strip_tags($new_instance['days']);
		$instance['tempunit']		=	$new_instance['tempunit'];
		$instance['windunit']		=	$new_instance['windunit'];
		$instance['search']			=	$new_instance['search'];
		$instance['lpadding']		=	$new_instance['lpadding'];
		$instance['rpadding']		=	$new_instance['rpadding'];
		$instance['disploc']		=	$new_instance['disploc'];
		$instance['dispicon']		=	$new_instance['dispicon'];
		$instance['dispdesc']		=	$new_instance['dispdesc'];
		$instance['disphighlow']	=	$new_instance['disphighlow'];
		$instance['dispwind']		=	$new_instance['dispwind'];
		$instance['disphumidity']	=	$new_instance['disphumidity'];
		$instance['disppressure']	=	$new_instance['disppressure'];
		$instance['dispcover']		=	$new_instance['dispcover'];
		$instance['dispvisibility']	=	$new_instance['dispvisibility'];
		$instance['disppop']		=	$new_instance['disppop'];

		return $instance;
	}

	// Display widget settings
	function form($instance) {

		// Setup defaults
		$defaults = array('title' => 'Weather', 'location' => 'Omaha', 'days' => '1', 'lpadding' => '0', 'rpadding' => '0');

		$instance = wp_parse_args((array)$instance, $defaults); ?>

		<div style="width: 47%; float: left; padding-right: 10px;">
			<h3><?php _e('General Settings', 'worldweather-pro'); ?></h3>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'worldweather-pro'); ?>:
					<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" type="text" class="widefat" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('apikey'); ?>"><?php _e('API Key', 'worldweather-pro'); ?>:
					<input id="<?php echo $this->get_field_id('apikey'); ?>" name="<?php echo $this->get_field_name('apikey'); ?>" value="<?php echo $instance['apikey']; ?>" type="text" class="widefat" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('location'); ?>"><?php _e('Location', 'worldweather-pro'); ?>:
					<input id="<?php echo $this->get_field_id('location'); ?>" name="<?php echo $this->get_field_name('location'); ?>" value="<?php echo $instance['location']; ?>" type="text" class="widefat" />
				</label>
			</p>
			<p style="display: none;">
				<label for="<?php echo $this->get_field_id('days'); ?>"><?php _e('Days to Display', 'worldweather-pro'); ?>:
					<input id="<?php echo $this->get_field_id('days'); ?>" name="<?php echo $this->get_field_name('days'); ?>" value="<?php echo $instance['days']; ?>" type="text" class="widefat" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('tempunit'); ?>"><?php _e('Temperature Unit', 'worldweather-pro'); ?>:
					<select id="<?php echo $this->get_field_id('tempunit'); ?>" name="<?php echo $this->get_field_name('tempunit'); ?>" class="widefat">
						<?php
							$options = array('fahrenheit', 'celsius');
							foreach($options as $option) {
								echo '<option value="' . $option . '"' .  ($instance['tempunit'] == $option ? ' selected' : '') . '>' . ucwords($option) . '</option>';
							}
						?>
					</select>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('windunit'); ?>"><?php _e('Wind Speed Unit', 'worldweather-pro'); ?>:
					<select id="<?php echo $this->get_field_id('windunit'); ?>" name="<?php echo $this->get_field_name('windunit'); ?>" class="widefat">
						<?php
							$options = array('mile', 'kilometer');
							foreach($options as $option) {
								echo '<option value="' . $option . '"' .  ($instance['windunit'] == $option ? ' selected' : '') . '>' . ucwords($option) . '</option>';
							}
						?>
					</select>
				</label>
			</p>
			<p style="display: none;">
				<label for="<?php echo $this->get_field_id('search'); ?>"><?php _e('Allow Inline Searching', 'worldweather-pro'); ?>:
					<select id="<?php echo $this->get_field_id('search'); ?>" name="<?php echo $this->get_field_name('search'); ?>" class="widefat">
						<?php
							$options = array('disabled', 'enabled');
							foreach($options as $option) {
								echo '<option value="' . $option . '"' . ($instance['search'] == $option ? ' selected' : '') . '>'  . ucwords($option) . '</option>';
							}
						?>
					</select>
				</label>
			</p>
			<br />
			<h3><?php _e('Styling Settings', 'worldweather-pro'); ?></h3>
			<p>
				<label for="<?php echo $this->get_field_id('lpadding'); ?>"><?php _e('Left Column Padding', 'worldweather-pro'); ?>:
					<input id="<?php echo $this->get_field_id('lpadding'); ?>" name="<?php echo $this->get_field_name('lpadding'); ?>" value="<?php echo $instance['lpadding']; ?>" type="text" class="widefat" />
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('rpadding'); ?>"><?php _e('Right Column Padding', 'worldweather-pro'); ?>:
					<input id="<?php echo $this->get_field_id('rpadding'); ?>" name="<?php echo $this->get_field_name('rpadding'); ?>" value="<?php echo $instance['rpadding']; ?>" type="text" class="widefat" />
				</label>
			</p>
		</div>

		<div style="width: 47%; float: left; padding-left: 10px; border-left: 1px solid #DFDFDF;">
			<h3><?php _e('Display Settings', 'worldweather-pro'); ?></h3>
			<p>
				<label for="<?php echo $this->get_field_id('disploc'); ?>"><?php _e('Display Location', 'worldweather-pro'); ?>:
					<select id="<?php echo $this->get_field_id('disploc'); ?>" name="<?php echo $this->get_field_name('disploc'); ?>" class="widefat">
						<?php
							$options = array('disabled', 'enabled');
							foreach($options as $option) {
								echo '<option value="' . $option . '"' . ($instance['disploc'] == $option ? ' selected' : '') . '>'  . ucwords($option) . '</option>';
							}
						?>
					</select>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('dispicon'); ?>"><?php _e('Display Indicator Icon', 'worldweather-pro'); ?>:
					<select id="<?php echo $this->get_field_id('dispicon'); ?>" name="<?php echo $this->get_field_name('dispicon'); ?>" class="widefat">
						<?php
							$options = array('disabled', 'enabled');
							foreach($options as $option) {
								echo '<option value="' . $option . '"' . ($instance['dispicon'] == $option ? ' selected' : '') . '>'  . ucwords($option) . '</option>';
							}
						?>
					</select>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('dispdesc'); ?>"><?php _e('Display Description', 'worldweather-pro'); ?>:
					<select id="<?php echo $this->get_field_id('dispdesc'); ?>" name="<?php echo $this->get_field_name('dispdesc'); ?>" class="widefat">
						<?php
							$options = array('disabled', 'enabled');
							foreach($options as $option) {
								echo '<option value="' . $option . '"' . ($instance['dispdesc'] == $option ? ' selected' : '') . '>'  . ucwords($option) . '</option>';
							}
						?>
					</select>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('disphighlow'); ?>"><?php _e('Display High/Low', 'worldweather-pro'); ?>:
					<select id="<?php echo $this->get_field_id('disphighlow'); ?>" name="<?php echo $this->get_field_name('disphighlow'); ?>" class="widefat">
						<?php
							$options = array('disabled', 'enabled');
							foreach($options as $option) {
								echo '<option value="' . $option . '"' . ($instance['disphighlow'] == $option ? ' selected' : '') . '>'  . ucwords($option) . '</option>';
							}
						?>
					</select>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('dispwind'); ?>"><?php _e('Display Wind', 'worldweather-pro'); ?>:
					<select id="<?php echo $this->get_field_id('dispwind'); ?>" name="<?php echo $this->get_field_name('dispwind'); ?>" class="widefat">
						<?php
							$options = array('disabled', 'enabled');
							foreach($options as $option) {
								echo '<option value="' . $option . '"' . ($instance['dispwind'] == $option ? ' selected' : '') . '>'  . ucwords($option) . '</option>';
							}
						?>
					</select>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('disphumidity'); ?>"><?php _e('Display Humidity', 'worldweather-pro'); ?>:
					<select id="<?php echo $this->get_field_id('disphumidity'); ?>" name="<?php echo $this->get_field_name('disphumidity'); ?>" class="widefat">
						<?php
							$options = array('disabled', 'enabled');
							foreach($options as $option) {
								echo '<option value="' . $option . '"' . ($instance['disphumidity'] == $option ? ' selected' : '') . '>'  . ucwords($option) . '</option>';
							}
						?>
					</select>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('disppressure'); ?>"><?php _e('Display Pressure', 'worldweather-pro'); ?>:
					<select id="<?php echo $this->get_field_id('disppressure'); ?>" name="<?php echo $this->get_field_name('disppressure'); ?>" class="widefat">
						<?php
							$options = array('disabled', 'enabled');
							foreach($options as $option) {
								echo '<option value="' . $option . '"' . ($instance['disppressure'] == $option ? ' selected' : '') . '>'  . ucwords($option) . '</option>';
							}
						?>
					</select>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('dispcover'); ?>"><?php _e('Display Cloud Cover', 'worldweather-pro'); ?>:
					<select id="<?php echo $this->get_field_id('dispcover'); ?>" name="<?php echo $this->get_field_name('dispcover'); ?>" class="widefat">
						<?php
							$options = array('disabled', 'enabled');
							foreach($options as $option) {
								echo '<option value="' . $option . '"' . ($instance['dispcover'] == $option ? ' selected' : '') . '>'  . ucwords($option) . '</option>';
							}
						?>
					</select>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('dispvisibility'); ?>"><?php _e('Display Visibility', 'worldweather-pro'); ?>:
					<select id="<?php echo $this->get_field_id('dispvisibility'); ?>" name="<?php echo $this->get_field_name('dispvisibility'); ?>" class="widefat">
						<?php
							$options = array('disabled', 'enabled');
							foreach($options as $option) {
								echo '<option value="' . $option . '"' . ($instance['dispvisibility'] == $option ? ' selected' : '') . '>'  . ucwords($option) . '</option>';
							}
						?>
					</select>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('disppop'); ?>"><?php _e('Display Probability of Precipitation', 'worldweather-pro'); ?>:
					<select id="<?php echo $this->get_field_id('disppop'); ?>" name="<?php echo $this->get_field_name('disppop'); ?>" class="widefat">
						<?php
							$options = array('disabled', 'enabled');
							foreach($options as $option) {
								echo '<option value="' . $option . '"' . ($instance['disppop'] == $option ? ' selected' : '') . '>'  . ucwords($option) . '</option>';
							}
						?>
					</select>
				</label>
			</p>
		</div>

		<div style="clear: both;"></div>
		<h3><?php _e('Info', 'worldweather-pro'); ?></h3>
		<p style="color: #999; font-style: italic; text-align: justify;"><?php echo sprintf(__('The WorldWeather Pro widget is powered by the World Weather Online API. If you don\'t already have a World Weather Online API key, you can get one for free <a href="%s" target="_blank">here</a>!', 'worldweather-pro'), 'http://www.worldweatheronline.com/register.aspx'); ?></p>
		<p style="color: #999; font-style: italic; text-align: justify;"><?php _e('WorldWeather Pro is not affiliated with, endorsed, or sponsored by World Weather Online. It is simply making use of an excellent, free API to bring you the weather in a simple, clean format.', 'worldweather-pro'); ?></p>
	<?php }
}
