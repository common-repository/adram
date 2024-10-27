<?php

add_action('admin_menu', 'adram_setup_menu');
add_action('admin_init', 'adram_register_settings');
add_action('plugins_loaded', 'adram_load_text_domain');

if (!function_exists('adram_load_text_domain')) {
    function adram_load_text_domain() {
        load_plugin_textdomain('adram', false, dirname(plugin_basename(__FILE__)).'/../lang/');
    }
}

if (!function_exists('adram_setup_menu')) {
    function adram_setup_menu() {
        add_options_page(__(ADRAM_PLUGIN_NAME.' Plugin Page', 'adram'), ADRAM_PLUGIN_NAME, 'manage_options', 'adram-plugin', 'adram_admin_init');
    }
}

if (!function_exists('adram_register_settings')) {
    function adram_register_settings() {
        register_setting('adram-options', 'adram-placement-id', [
            'type' => 'string',
            'description' => ADRAM_PLUGIN_NAME.' service placement ID',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => NULL,
            'show_in_rest' => false,
        ]);
        register_setting('adram-options', 'adram-placement-cache-lifetime', [
            'type' => 'integer',
            'description' => ADRAM_PLUGIN_NAME.' placement cache lifetime in seconds',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 86400 * 7, // 1 week
            'show_in_rest' => false,
        ]);
        register_setting('adram-options', 'adram-api-key', [
            'type' => 'string',
            'description' => ADRAM_PLUGIN_NAME.' API Key',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
            'show_in_rest' => false,
        ]);
        register_setting('adram-options', 'adram-debug-mode', [
            'type' => 'boolean',
            'description' => ADRAM_PLUGIN_NAME.' debug mode',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 0,
            'show_in_rest' => false,
        ]);
        register_setting('adram-options', 'adram-testing-server', [
            'type' => 'string',
            'description' => ADRAM_PLUGIN_NAME.' server',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ADRAM_HOSTNAME,
            'show_in_rest' => false,
        ]);
    }
}

if (!function_exists('adram_admin_init')) {
    function adram_admin_init () {
        ?>
        <style>
        .adram-banner{
            position: relative;
            max-width: 1544px;
        }
        .adram-banner>img{
            height: 100%;
            width: 100%;
            max-height: 500px;
            max-width: 1544px;
        }
        .adram-banner .buttons {
            position: absolute;
            left: 10%;
            top: 60%;
            width: auto;
            display: inline-block;
        }
        .adram-banner .buttons a {
            text-decoration: none;
        }
        .adram-banner .btn {
            color: #fff;
            display: inline-block;
            text-align: center;
            border-radius: 5px;
            height: 60px;
            font-size: 16pt;
            line-height: 56px;
            vertical-align: middle;
        }
        .adram-banner .btn.reg {
            background: #b82825;
            width: 470px;
            height: 60px;
        }
        .adram-banner .btn.reg:hover {
            background: #b71c1c;
        }
        .adram-banner .btn.info {
            border: solid #fff 2px;
            height: 56px;
            margin-left: 23px;
        }
        .adram-banner .btn.info:hover {
            color: #b82825;
            background: #fff;
        }
        .adram-banner .btn.info.about {
            width: 220px;
        }
        .adram-banner .btn.info.faq {
            width: 112px;
        }
        </style>
        <div class="wrap">
        <div class="adram-banner">
            <img src="/wp-content/plugins/adram/images/banner-1544x500.png" alt="adram">
            <div class="buttons">
                <a href="https://<?php echo ADRAM_HOSTNAME ?>/join" target="_blank">
                    <div class="btn reg"><?php _e('Register & start Recover your Revenue', 'adram') ?></div>
                </a>
                <a href="https://<?php echo ADRAM_HOSTNAME ?>/about/project" target="_blank">
                    <div class="btn info about"><?php _e('About AdRam', 'adram') ?></div>
                </a>
                <a href="https://<?php echo ADRAM_HOSTNAME ?>/about/project" target="_blank">
                    <div class="btn info faq"><?php _e('FAQ', 'adram') ?></div>
                </a>
            </div>
        </div>
        <h1><?php _e(ADRAM_PLUGIN_NAME.' Plugin', 'adram') ?></h1>
        <form method="post" action="options.php">
        <?php settings_fields('adram-options'); ?>
        <?php do_settings_sections('adram-options'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e(ADRAM_PLUGIN_NAME.' API Key', 'adram') ?>:</th>
                <td><input type="text" required name="adram-api-key" value="<?php echo esc_attr(get_option('adram-api-key')) ?>">&nbsp;<a href="https://<?php echo ADRAM_HOSTNAME ?>/profile#api_key"><?php _e('Get your API Key', 'adram') ?></a></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Cache Lifetime (sec)', 'adram') ?>:</th>
                <td><input type="number" name="adram-placement-cache-lifetime" value="<?php echo esc_attr(get_option('adram-placement-cache-lifetime')) ?>"></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e(ADRAM_PLUGIN_NAME.' debug mode', 'adram') ?>:</th>
                <td><input type="checkbox" name="adram-debug-mode" value="1" <?php checked(esc_attr(get_option('adram-debug-mode'))) ?>></td>
            </tr>
            <?php if(isset($_COOKIE['adram-testing'])): ?>
            <tr valign="top">
                <th dcope="row"><?php _e(ADRAM_PLUGIN_NAME.' Server', 'adram') ?>:</th>
                <td>
                    <select name="adram-testing-server">
                        <option value="<?php echo ADRAM_HOSTNAME ?>" <?php selected(get_option('adram-testing-server'), ADRAM_HOSTNAME) ?>><?php echo ADRAM_HOSTNAME ?></option>
                        <option value="beta.adram.media" <?php selected(get_option('adram-testing-server'), 'beta.adram.media') ?>>beta.adram.media</option>
                        <option value="experimental.adram.media" <?php selected(get_option('adram-testing-server'), 'experimental.adram.media') ?>>experimental.adram.media</option>
                        <option value="adram.local" <?php selected(get_option('adram-testing-server'), 'adram.local') ?>>adram.local</option>
                    </select>
                </td>
            </tr>
            <?php endif; ?>
        </table>
        <?php submit_button(__('Update Settings', 'adram')); ?>
        </form>
        <?php
        if (get_option('adram-debug-mode')) {
            adram_show_debug_info();
        } else {
            adram_drop_log();
        }
    }
}

if (!function_exists('adram_drop_log')) {
    function adram_drop_log() {
        $logfilePath = plugin_dir_path(__FILE__) . '../adram.log';
        if (file_exists($logfilePath)) {
            unlink($logfilePath);
        }
    }
}

if (!function_exists('adram_show_debug_info')) {
    function adram_show_debug_info() {
        $CachePath = plugin_dir_path(__FILE__) . '../cache';
        $files = glob($CachePath.'/*');
        echo "<hr><h2>".__('Debug output', 'adram').":</h2>";
        echo "<h3>".__('Cache', 'adram').":</h3>";
        foreach ($files as $f) {
            $filename = basename($f);

            if (preg_match('/.*[^(php)]$/', $filename))
                echo '<a href="'.strstr($f, '/wp-content').'" target="_blank">' . $filename . '</a><br>';
        }

        echo "<br><h3>".__('Log', 'adram').":</h3>";
        echo '<a href="/wp-content/plugins/adram/adram.log" target="_blank">adram.log</a><br>'; 
    }
}
