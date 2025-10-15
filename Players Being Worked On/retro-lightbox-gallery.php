<?php
/**
 * Plugin Name: Retro Lightbox Gallery for Elementor
 * Description: A retro-styled lightbox image gallery with taxonomy filtering and social sharing
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: retro-gallery
 */

if (!defined('ABSPATH')) exit;

final class Retro_Gallery_Elementor {
    
    const VERSION = '1.0.0';
    const MINIMUM_ELEMENTOR_VERSION = '3.0.0';
    const MINIMUM_PHP_VERSION = '7.0';

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
    }

    public function init() {
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return;
        }

        if (!version_compare(ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
            return;
        }

        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
            return;
        }

        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }

    public function admin_notice_missing_elementor() {
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'retro-gallery'),
            '<strong>' . esc_html__('Retro Gallery', 'retro-gallery') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'retro-gallery') . '</strong>'
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    public function admin_notice_minimum_elementor_version() {
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'retro-gallery'),
            '<strong>' . esc_html__('Retro Gallery', 'retro-gallery') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'retro-gallery') . '</strong>',
            self::MINIMUM_ELEMENTOR_VERSION
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    public function admin_notice_minimum_php_version() {
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'retro-gallery'),
            '<strong>' . esc_html__('Retro Gallery', 'retro-gallery') . '</strong>',
            '<strong>' . esc_html__('PHP', 'retro-gallery') . '</strong>',
            self::MINIMUM_PHP_VERSION
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    public function register_widgets($widgets_manager) {
        require_once(__DIR__ . '/widgets/retro-gallery-widget.php');
        $widgets_manager->register(new \Retro_Gallery_Widget());
    }
}

Retro_Gallery_Elementor::instance();