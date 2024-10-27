<?php
/**
 * Plugin Name: AdRam
 * Description: Retrieve AdRam placements by current page URL
 * Text Domain: AdRam
 * Version: 0.4.1 
 * Author: adram.media
 * Author URI: https://adram.media
 */

define('ADRAM_PLUGIN_NAME', 'AdRam');
define('ADRAM_HOSTNAME', 'adram.media');

require_once plugin_dir_path(__FILE__) . 'includes/adram.php';
require_once plugin_dir_path(__FILE__) . 'includes/widget.php';

add_action( 'widgets_init', function() { register_widget( 'AdRam_Widget' ); } );
add_action('wp_enqueue_scripts', 'adram_scripts', 0);
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'adram_action_links' );

if (!function_exists('adram_action_links')) {
    function adram_action_links($links) {
        $links[] = '<a href="'.admin_url('options-general.php?page='.strtolower(ADRAM_PLUGIN_NAME).'-plugin').'">Settings</a>';
        return $links;
    }
}

if (!function_exists('adram_scripts')) {
    function adram_scripts() {
        echo '<script>console.log("'.ADRAM_PLUGIN_NAME.' version: 0.4.1")</script>';
        if (is_active_widget(false, false, "adram_widget")) {
            adram_log(['Event' => 'DETERMINE OUTPUT METHOD', 'Output method' => 'Widget']);
        } else {
            adram_log(['Event' => 'DETERMINE OUTPUT METHOD', 'Output method' => 'Footer']);
            $urls = adram_get_placement_urls();
            adram_log(['Event' => 'PLACE SCRIPTS ON PAGE', 'Current Page' => adram_get_current_url(), 'URLS' => $urls]); 
            $script_name = '';
            foreach ($urls as $key => $url) {
                $script_name = 'adram-placement-script-'.$key;
                wp_register_script($script_name, $url, [], null, true);
                wp_enqueue_script($script_name);
            }
        }
    }
}

if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'admin/adram-admin.php';
} else {
    adram_log(['Event' => 'PLUGIN INIT '.ADRAM_PLUGIN_NAME, 'Version' => '0.4.1']);
}
