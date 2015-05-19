<?php

require_once 'includes/report_post_creator.php';

class FishMapPostReportPlugin
{
    public function __construct()
    {
        add_action('wp_ajax_fish_map_save_report', array($this, 'saveReport'));
        add_action('wp_ajax_nopriv_fish_map_save_report', array($this, 'saveReport'));
        add_action('wp_ajax_save_photos', array($this, 'savePhotos'));
        add_action('wp_ajax_nopriv_save_photos', array($this, 'savePhotos'));

        add_shortcode('fish-map-report-form', array($this, 'renderForm'));
    }

    public function addScripts()
    {
        wp_deregister_script('google-map');
        wp_register_script('google-map', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCByg67-8HjM_17CVdq9iOiN95Nhz7izCw&sensor=false&language=uk');
        wp_enqueue_script('google-map');

        wp_register_script('jquery.blockui', plugins_url('js/3p/jquery.blockUI.js', __FILE__));
        wp_enqueue_script('jquery.blockui');

        wp_register_script('jquery.scrollTo', plugins_url('js/3p/jquery.scrollTo.js', __FILE__));
        wp_enqueue_script('jquery.scrollTo');

        /* plupload */
        wp_register_script('jquery.plupload', plugins_url('js/3p/plupload-2.1.1/plupload.full.min.js', __FILE__));
        wp_enqueue_script('jquery.plupload');
        wp_register_script('jquery.plupload.uk', plugins_url('js/3p/plupload-2.1.1/i18n/uk_UA.js', __FILE__));
        wp_enqueue_script('jquery.plupload.uk');

        wp_register_script('report_post', plugins_url('js/report_post.js', __FILE__));
        wp_enqueue_script('report_post');
    }

    public function addStylesheets()
    {
        wp_register_style('report_post', plugins_url('css/report_post.css', __FILE__));
        wp_enqueue_style('report_post');

        // qTip2
        wp_register_style('jquery.qtip2', 'http://cdn.jsdelivr.net/qtip2/2.2.1/jquery.qtip.min.css');
        wp_enqueue_style('jquery.qtip2');
    }

    public function renderForm($attr)
    {
        $this->addScripts();
        $this->addStylesheets();

        $text_editor = $this->_getRichTextEditor();
        include 'tpls/report_form.phtml';
    }

    private function _getRichTextEditor()
    {
        $settings = array(
            'media_buttons' => false,
            'teeny'         => true,
            'wpautop'       => true,
            'quicktags'     => true,
            'editor_class'  => 'report-form-editor',
            'textarea_rows' => 15
        );
       
        ob_start();
        wp_editor('','report_content', $settings);
        $wp_editor = ob_get_contents();
        ob_end_clean();
        return $wp_editor;
    }

    public function saveReport()
    {
        $data = array_intersect_key($_POST, array_flip(array('name', 'report_content', 'photos')));
        $data = stripslashes_deep($data);
        $reportCreator = new ReportPostCreator($data);
        if ($reportCreator->validator->validate()) {
            $postId = $reportCreator->createReportPost();
            $this->_sendEmailNotification($postId);

            $response = array(
                'error' => false,
                'permalink' => get_permalink($postId)
            );
        } else {
            $response = array(
                'error' => true,
                'errors' => $reportCreator->validator->errors()
            );
        }
        echo json_encode($response);
        die();
    }

    private function _sendEmailNotification($postId)
    {
        $subject = '[Рибні місця Рівненщини] Додано звіт про рибалку';
        $message = 'Додано звіт про рибалку.' . "\n\n"
                 . "Редагувати в базі:  http://rivnefish.com/wp-admin/post.php?post={$postId}&action=edit \n"
                 . 'Дата:' . date("d M Y H:i:s") . "\n"
                 . 'IP: ' . $_SERVER['REMOTE_ADDR'] . "\n";
        $headers = 'From: ' . FROM_EMAIL;
        wp_mail(TO_EMAIL, $subject, $message, $headers);
    }
}

$fishMapPostReportPlugin = new FishMapPostReportPlugin();
