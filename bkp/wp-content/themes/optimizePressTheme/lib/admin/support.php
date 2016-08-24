<?php

/**
 * OptimizePress support page
 *
 * @author OptimizePress <info@optimizepress.com>
 */
class OptimizePress_Support
{
    protected $sections;
    protected $error;
    protected $notification;

    /**
     * Add menu page action
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'addMenuPage'));
    }

    /**
     * Register menu page
     * @return void
     */
    public function addMenuPage()
    {
        $page = add_submenu_page(OP_SN, __('Support', OP_SN), __('Support', OP_SN), 'edit_theme_options', OP_SN . '-support', array($this, 'displayPage'));

        add_action('load-' . $page, array($this, 'initSections'));
        add_action('load-' . $page, array($this, 'saveSections'));
        add_action('admin_print_styles-' . $page, array($this,'printScripts'));
        add_action('admin_footer-' . $page, array($this,'printFooterScripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));
    }

    /**
     * Enqueue scripts for support page only (ZeroClipboard)
     * @param  string $hook
     * @return void
     */
    public function enqueueScripts($hook)
    {
        if ($hook !== 'optimizepress_page_optimizepress-support') {
            return;
        }

        wp_enqueue_script(OP_SN . '-zeroclipboard', OP_JS . 'zeroclipboard/ZeroClipboard.min.js', array(OP_SCRIPT_BASE), OP_VERSION);
        wp_register_script(OP_SN . '-support', OP_JS . 'support' . OP_SCRIPT_DEBUG . '.js', array(OP_SN . '-zeroclipboard'), OP_VERSION);
        wp_localize_script(OP_SN . '-support', 'opSupportPageL10N', array('copied_to_clipboard' => __('Copied to Clipboard!', OP_SN)));
        wp_enqueue_script(OP_SN . '-support');
    }

    /**
     * Print head scripts
     * @return void
     */
    public function printScripts()
    {
        op_print_scripts('support');
        wp_enqueue_style(OP_SN . '-admin-common', false, false, OP_VERSION);

        op_enqueue_backend_scripts();
        // if (OP_SCRIPT_DEBUG === '') {
            // wp_enqueue_script(OP_SN . '-admin-common', false, array(OP_SN . '-noconflict-js'), OP_VERSION);
        // } else {
        //     wp_enqueue_script(OP_SN.'-op-back-all', OP_JS.'op-back-all.js', array(OP_SN.'-op-jquery-base-all', 'jquery-ui-slider', 'farbtastic', 'jquery-ui-position', 'media-upload', 'thickbox', 'jquery-ui-sortable', 'swfupload-swfobject'), OP_VERSION);
        // }
    }

    /**
     * Print footer scripts
     * @return void
     */
    public function printFooterScripts()
    {
        op_print_footer_scripts('support');
    }

    /**
     * Show support page with its sectinos
     *
     * Load "support/index" template
     * @return void
     */
    public function displayPage()
    {
        $tabs = $tabContent = array();

        foreach ($this->sections as $key => $section) {
            $tabs[$key] = array(
                'title'     => $section['title'],
                'prefix'    => '',
                'li_class'  => op_has_section_error($key) ? 'has-error' : '',
            );

            $tabContent[$key] = op_tpl('support/step', array('section_type' => $key, 'sections' => $section['object']->sections()));
        }

        $data = array(
            'tabs'          => $tabs,
            'tab_content'   => $tabContent,
            'module_name'   => 'support',
            'error'         => $this->error,
            'notification'  => $this->notification,
        );

        echo op_tpl('support/index', array('content' => op_tpl('generic/tabbed_module', $data)));
    }

    /**
     * Initialize sections
     *
     * @return void
     */
    public function initSections()
    {
        $sections = array(
            'system_status'             => __('System Status', OP_SN),
            'disable_styles_scripts'    => __('Disable Styles and Scripts', OP_SN),
            'cache'                     => __('OptimizePress Cache', OP_SN),
            // 'tickets'                   => __('Support Tickets', OP_SN),
        );

        foreach ($sections as $key => $title) {
            require_once OP_LIB . 'sections/support/' . $key . '.php';

            $class = 'OptimizePress_Sections_' . ucfirst($key);

            $this->sections[$key] = array(
                'title'     => $title,
                'object'    => new $class(),
                'image'     => $key . '-icon.png'
            );
        }
    }

    /**
     * Save page sections
     *
     * Run each section "save_action" callback which deals with saving of data
     *
     * @return void
     */
    public function saveSections()
    {
        if (isset($_POST[OP_SN . '_support'])) {
            $op         = $_POST['op'];
            $opSections = op_get_var($op, 'sections', array());

            foreach ($this->sections as $name => $section) {
                $sections = $section['object']->sections();

                foreach($sections as $sectionName => $sectionData) {
                    if (is_array($sectionData)) {
                        if (isset($sectionData['save_action'])) {
                            call_user_func_array($sectionData['save_action'], array($opSections));
                        }

                        if (isset($sectionData['module'])) {
                            $modOps = op_get_var($op,$sectionName, array());
                            $opts   = op_get_var($sectionData, 'options', array());
                            op_mod($sectionData['module'])->save_settings($sectionName, $opts, $modOps);
                        }
                    }
                }
            }

            if (op_has_error()) {
                $this->error        = __('There was a problem processing the form, please review the errors below', OP_SN);
            } else {
                $this->notification = __('Your settings have been updated.', OP_SN);
            }
        }
    }
}

new OptimizePress_Support();