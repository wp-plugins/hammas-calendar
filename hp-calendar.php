<?php 
/* 
Plugin Name: Hammas Calendar
Description: Hammas WordPress integration
Version: 1.3.2
Author: Innovaatik Grupp OÜ
Author URI: http://www.innomed.ee

Text Domain:   hp-calendar
Domain Path:   /lang/
*/
define('HP_PLUGIN_VERSION', '1.3.2');
define('HP_CALENDAR_SCRIPT', '/js/hp-calendar-min.js'); 
define('HP_CALENDAR_STYLE', '/css/hp-calendar-min.css'); 
define('HP_MANAGE_URL',  admin_url('admin-ajax.php') . '?action=hp_calendar_request&request=manage');

register_activation_hook(__FILE__, 'HP_Calendar_activate');
function HP_Calendar_activate() {
  add_option('hp_calendar_api_key', __('YOUR API_KEY', 'hp-calendar'));
  add_option('hp_calendar_host', __('API HOST', 'hp-calendar'));
  add_option('hp_calendar_callback', get_home_url());
  add_option('hp_calendar_homepage', get_home_url());
  add_option('hp_calendar_manage', admin_url('admin-ajax.php') . '?action=hp_calendar_request&request=manage');
  add_option('hp_calendar_logo', __('LINK TO YOUR LOGO', 'hp-calendar'));
  add_option('hp_calendar_company', 'YOUR COMPANY NAME');
  add_option('hp_calendar_language', 'auto');
  add_option('hp_calendar_use_default_style', 1);
}

register_deactivation_hook(__FILE__, 'HP_Calendar_deactivate');
function HP_Calendar_deactivate() {
  delete_option('hp_calendar_api_key');
  delete_option('hp_calendar_host');
  delete_option('hp_calendar_callback');
  delete_option('hp_calendar_homepage');
  delete_option('hp_calendar_manage');
  delete_option('hp_calendar_logo');
  delete_option('hp_calendar_company');
  delete_option('hp_calendar_language');  
  delete_option('hp_calendar_use_default_style');
}

add_shortcode('hp-calendar', 'HP_Calendar_display_calendar');
function HP_Calendar_display_calendar($atts) {
  HP_Calendar_registerScripts();
  ob_start();  
  $atts = shortcode_atts(array(
        'clinics' => '',
        'default_service' => ''
	), $atts);

?>
  <div class="hp-calendar-container">
    <?php
        printf("<div class=\"hp-calendar\" data-clinics=\"%s\" data-default_service=\"%s\" data-metainfo=\"%s\">", 
            $atts['clinics'], 
            $atts['default_service'],
            base64_encode( HP_PLUGIN_VERSION )      
            );
    ?>
    </div>
    <div class="hp-calendar-info">
    </div>
  </div>
<?php

  $content = ob_get_contents();
  ob_end_clean();

  return $content;
}

add_shortcode('hp-calendar-manage-url', 'HP_Calendar_manage_url');
function HP_Calendar_manage_url($atts) {
	if( trim(get_option('hp_calendar_manage')) == "" )
		return HP_MANAGE_URL;
	else 
		return get_option('hp_calendar_manage');
}

add_shortcode('hp-calendar-manage-redirect', 'HP_Calendar_manage_redirect');
function HP_Calendar_manage_redirect($atts) {
  $url = admin_url('admin-ajax.php') . '?action=hp_calendar_request&request=manage';
  $code = '<script>setTimeout(function () { document.location.replace("' . $url . '"); }, 1250);</script>';
  return $code;
}

add_action('init', 'HP_Calendar_init');
function HP_Calendar_init() {
  load_plugin_textdomain( 'hp-calendar', false, dirname(plugin_basename(__FILE__)) . '/lang/');

  $host = get_option('hp_calendar_host');

  wp_register_script('HP_Calendar', $host . HP_CALENDAR_SCRIPT, array('jquery'), null, true);
  wp_register_script('HP_Calendar_insert', plugins_url('hp-calendar-insert-min.js', __FILE__),
    array('jquery', 'HP_Calendar'), null, true);

  wp_register_style('HP_Calendar_style', $host . HP_CALENDAR_STYLE);
}
//This hook is deprecated since we only want to load booking scripts/styles 
//when plugin is displayed. 
//add_action('wp_enqueue_scripts', 'HP_Calendar_registerScripts');
function HP_Calendar_registerScripts() {
  wp_enqueue_script('HP_Calendar_insert');
  $lang = get_option('hp_calendar_language') == 'auto' ? substr(get_bloginfo ( 'language' ), 0, 2) : get_option('hp_calendar_language');
  wp_localize_script('HP_Calendar', 'HP_Calendar_data', array(
    'strings' => $lang,
    'ajaxurl' => admin_url('admin-ajax.php')
  ));

  if (get_option('hp_calendar_use_default_style')) {
    wp_enqueue_style('HP_Calendar_style');
  }
}

add_action('wp_ajax_hp_calendar_request', 'HP_Calendar_handleRequest');
add_action('wp_ajax_nopriv_hp_calendar_request', 'HP_Calendar_handleRequest');
function HP_Calendar_handleRequest() {
  $host = get_option('hp_calendar_host');
  $request = array_merge($_POST, $_GET);
  $request['api_key'] = get_option('hp_calendar_api_key');

  if($request['request'] == 'redirect' || $request['request'] == 'manage') {
    $request['callback'] = get_option('hp_calendar_callback');
	if(!get_option('hp_calendar_logo'))
		$request['logo'] = $host  . '/img/empty.png';
	else  
		$request['logo'] = get_option('hp_calendar_logo');
    $request['company'] = get_option('hp_calendar_company');
    $request['homepage'] = get_option('hp_calendar_homepage');
    $request['manage'] = trim(get_option('hp_calendar_manage')) == "" ? HP_MANAGE_URL : get_option('hp_calendar_manage');
  }
  $header = 'Content-Type: application/json' . PHP_EOL;
  $ref = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'];
  $header .= sprintf('Referer: %s', $ref); 
  $opts = array('http' => array(
    'method'  => 'POST',
    'header'  => $header,
    'content' => json_encode($request)
  ));

  $context = stream_context_create($opts);

  $result = file_get_contents($host . '/api', false, $context);

  if ($request['request'] == 'manage') {
    $res = json_decode($result, true);
    if ($res['result'] == 'success') {
      header('Location: ' . $host . '/manage/new/' . $res['sid']);
      exit;
    }
  }

  echo $result;
  exit;
}

add_action('admin_init', 'HP_Calendar_admin_init');
function HP_Calendar_admin_init() {
  register_setting('hp-calendar-group', 'hp_calendar_api_key');
  register_setting('hp-calendar-group', 'hp_calendar_host');
  register_setting('hp-calendar-group', 'hp_calendar_callback');
  register_setting('hp-calendar-group', 'hp_calendar_homepage');
  register_setting('hp-calendar-group', 'hp_calendar_manage');
  register_setting('hp-calendar-group', 'hp_calendar_logo');
  register_setting('hp-calendar-group', 'hp_calendar_company');
  register_setting('hp-calendar-group', 'hp_calendar_language');
  register_setting('hp-calendar-group', 'hp_calendar_use_default_style');
}

add_action('admin_menu', 'HP_Calendar_menu');
function HP_Calendar_menu() {
	add_options_page( __('Hammas Calendar Options', 'hp-calendar'), __('Hammas Calendar', 'hp-calendar'), 'manage_options', 'hp-calendar', 'HP_Calendar_options' );
}

function HP_Calendar_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die(__('You do not have sufficient permissions to access this page.', 'hp-calendar'));
  }

?>

<div class="wrap">
<h2><?php _e('Hammas Calendar', 'hp-calendar') ?></h2>
<hr>
<p>
    <?php _e('Use the <strong>[hp-calendar]</strong> shortcode to show the calendar. If needed use clinics attribute to choose '
    . 'which clinics are visible. Clinics need to be defined in comma-separated list of clinic IDs or names (I.e [hp-calendar clinics="1,2,ClinicName3,ClinicName4"]).'
    . 'Also to auto-select a default service you can set up default_service attribute. It can take one or more service codes as input.'
    . '(I.e [hp-calendar default_service="1002,3,4"]).'
    . '<br>Add  &lt;a href="<strong>[hp-calendar-manage-url]</strong>"&gt; to show a link to the appointment managing interface. '
    . 'Use the <strong>[hp-calendar-manage-redirect]</strong> shortcode to redirect the user to the appointment managing interface.', 'hp-calendar') ?>
</p>
<hr>
<form method="post" action="options.php">
  <?php settings_fields('hp-calendar-group'); ?>
  <table class="form-table">
    <tr valign="top">
      <th scope="row">
        <label for="setting_a"><?php _e('API Key', 'hp-calendar') ?></label>
      </th>
      <td>
        <input type="text" name="hp_calendar_api_key" class="regular-text" value="<?php echo get_option('hp_calendar_api_key'); ?>" />
        <p class="description"><?php _e('This can be found in the Manager->Web dialog in Hammas.', 'hp-calendar') ?></p>
      </td>
    </tr>
    <tr valign="top">
      <th scope="row">
        <label for="setting_a"><?php _e('API Host', 'hp-calendar') ?></label>
      </th>
      <td>
        <input type="text" name="hp_calendar_host" class="regular-text" value="<?php echo get_option('hp_calendar_host'); ?>" />
        <p class="description"><?php _e('This can be found in the Manager->Web dialog in Hammas.', 'hp-calendar') ?></p>
      </td>
    </tr>
    <tr valign="top">
      <th scope="row">
        <label for="setting_b"><?php _e('Company name', 'hp-calendar') ?></label>
      </th>
      <td>
        <input type="text" name="hp_calendar_company" class="regular-text" value="<?php echo get_option('hp_calendar_company'); ?>" />
        <p class="description"><?php _e('The name of your company which will be shown in the footer.', 'hp-calendar') ?></p>
      </td>
    </tr>
    <tr valign="top">
      <th scope="row">
        <label for="setting_b"><?php _e('Return URL', 'hp-calendar') ?></label>
      </th>
      <td>
        <input type="text" name="hp_calendar_callback" class="regular-text" value="<?php echo get_option('hp_calendar_callback'); ?>" />
        <p class="description"><?php _e('This is the location where clients will be sent to after they finish booking/managing appointments.', 'hp-calendar') ?></p>
      </td>
    </tr>
    <tr valign="top">
      <th scope="row">
        <label for="setting_b"><?php _e('Homepage URL', 'hp-calendar') ?></label>
      </th>
      <td>
        <input type="text" name="hp_calendar_homepage" class="regular-text" value="<?php echo get_option('hp_calendar_homepage'); ?>" />
        <p class="description"><?php _e('This is where clients will be sent to when they click your logo in the header or your company name in the footer.', 'hp-calendar') ?></p>
      </td>
    </tr>
    <tr valign="top">
      <th scope="row">
        <label for="setting_b"><?php _e('Manage URL', 'hp-calendar') ?></label>
      </th>
      <td>
        <input type="text" name="hp_calendar_manage" class="regular-text" value="<?php echo get_option('hp_calendar_manage'); ?>" />
        <p class="description"><?php _e('Only change this if you have set up a better-looking rewrite or have created a page with the [hp-calendar-manage-redirect] shortcode.', 'hp-calendar') ?></p>
      </td>
    </tr>
    <tr valign="top">
      <th scope="row">
        <label for="setting_b"><?php _e('Logo URL', 'hp-calendar') ?></label>
      </th>
      <td>
        <input type="text" name="hp_calendar_logo" class="regular-text" value="<?php echo get_option('hp_calendar_logo'); ?>" />
        <p class="description"><?php _e('The location of your company logo which will be shown in the header (recommended height: 26px, will be scaled automatically).', 'hp-calendar') ?></p>
      </td>
    </tr>
    <tr valign="top">
      <th scope="row">
        <label for="setting_b"><?php _e('Language', 'hp-calendar') ?></label>
      </th>
      <td>
		<?php echo get_option('hp_calendar_logo') == 'auto' ? 'selected' : ''; ?>      
      	<select class="mceListBox" name="hp_calendar_language" >
	      	<option value="auto" <?php echo get_option('hp_calendar_language') == 'auto' ? 'selected' : ''; ?>  >Automatic</option>
	      	<option value="en" <?php echo get_option('hp_calendar_language') == 'en' ? 'selected' : ''; ?>  >English</option>
	      	<option value="et" <?php echo get_option('hp_calendar_language') == 'et' ? 'selected' : ''; ?>  >Eesti</option>
	      	<option value="ru" <?php echo get_option('hp_calendar_language') == 'ru' ? 'selected' : ''; ?>  >Русский</option>
      	</select>
        <p class="description"><?php _e('Select language if you want to force a certain language. Otherwise wordpress localization settings will be used.', 'hp-calendar') ?></p>
      </td>
    </tr>    
    <tr valign="top">
      <th scope="row">
        <label for="setting_b"><?php _e('Use default style', 'hp-calendar'); ?></label>
      </th>
      <td>
        <input type="checkbox" name="hp_calendar_use_default_style" value="1" class="code" <?php checked(1, get_option('hp_calendar_use_default_style'), true); ?> />
        <p class="description"><?php _e('Untick this if you have a custom stylesheet for the calendar.', 'hp-calendar') ?></p>
      </td>
    </tr>
  </table>
  <?php @submit_button(); ?>
</form> 
</div>
<?php
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'HP_Calendar_settings_link');
function HP_Calendar_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=hp-calendar">' . __('Settings', 'hp-calendar') . '</a>';
  array_unshift($links, $settings_link);
  return $links;
}
