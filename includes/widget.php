<?php

require_once 'adram.php';

class AdRam_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'adram_widget',
            ADRAM_PLUGIN_NAME.' widget',
            ['description' => __(ADRAM_PLUGIN_NAME.' widget', 'adram')]
        );
    }

    public function widget($args, $instance) {
        $urls = adram_get_placement_urls();
        foreach ($urls as $url) {
            echo '<script src="'.$url.'"></script>';
        }
    }

    public function form($instance) {

    }

    public function update($new_instance, $old_instance) {
        return [];
    }
}