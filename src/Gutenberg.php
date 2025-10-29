<?php

namespace Bb\BbFaq;

class Gutenberg
{
    public const BUILD_DIR = BB_FAQ__PLUGIN_PATH . 'build/blocks/';
    public const BUILD_URL = BB_FAQ__PLUGIN_URL . 'build/blocks/';
    public const JS_HANDLER = 'bb-faq';

    /**
     * Статичная инициализация класса.
     *
     * @return void
     */
    public static function init(): void
    {
        static::initAssets();
        static::initBlocks();
        static::registerMeta();
        static::registerHooks();
    }

    /**
     * Инициализация скриптов и стилей.
     *
     * @return void
     */
    private static function initAssets(): void
    {
        $assets = static::BUILD_DIR . 'index.asset.php';
        if (!file_exists($assets)) {
            return;
        }

        $settings = include $assets;

        add_action('admin_enqueue_scripts', function () use ($settings) {
            $screen = get_current_screen();
            if ($screen->base !== 'post') {
                return;
            }

            if (file_exists(static::BUILD_DIR . 'index.js')) {
                wp_enqueue_script(
                    static::JS_HANDLER,
                    static::BUILD_URL . 'index.js',
                    $settings['dependencies'],
                    $settings['version']
                );
            }

            if (file_exists(static::BUILD_DIR . 'index.css')) {
                wp_enqueue_style(
                    static::JS_HANDLER,
                    static::BUILD_URL . 'index.css',
                    [],
                    $settings['version']
                );
            }
        });
    }

    /**
     * Инициализация блоков Гутенберг в админке.
     * Необходимо так как на ЛХ появляются блоки
     * только из специального белого листа.
     * Этим хуком мы добавляем блоки в этот белый лист.
     *
     * @return void
     */
    private static function initBlocks(): void
    {
        $class = '\Lifehacker\GutenbergBlocks\App';
        $get = 'getBlocks';
        $add = 'addBlocks';
        if (!class_exists($class) || !method_exists($class, $get) || !method_exists($class, $add)) {
            return;
        }
        $blocks = $class::$get(BB_FAQ__PLUGIN_FILE, 'blocks');
        $class::$add($blocks);
    }

    /**
     * Регистрация хуков.
     *
     * @return void
     */
    private static function registerHooks(): void
    {
    }

    /**
     * Регистрация мета-полей.
     *
     * @return void
     */
    public static function registerMeta(): void
    {
    }

}
