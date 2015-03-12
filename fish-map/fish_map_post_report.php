<?php

require_once 'includes/Valitron/Validator.php';

use Valitron\Validator;
Validator::langDir(__DIR__ . '/includes/Valitron');

class FishMapPostReportPlugin
{
    private $_markerModel;
    private $_fishModel;

    public function __construct()
    {
        add_action('wp_ajax_fish_map_save_report', array($this, 'saveReport'));
        add_action('wp_ajax_nopriv_fish_map_save_report', array($this, 'saveReport'));
        add_action('wp_ajax_save_photos', array($this, 'savePhotos'));
        add_action('wp_ajax_nopriv_save_photos', array($this, 'savePhotos'));

        add_shortcode('fish-map-report-form', array($this, 'renderForm'));

        $this->_markerModel = new MarkerModel();
        $this->_fishModel = new FishModel();
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

        wp_register_script('post_report', plugins_url('js/post_report.js', __FILE__));
        wp_enqueue_script('post_report');
    }

    public function addStylesheets()
    {
        wp_register_style('post_report', plugins_url('css/post_report.css', __FILE__));
        wp_enqueue_style('post_report');

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
        $validator = $this->_validator($_POST);
        if ($validator->validate()) {
            $galleryId = $this->_createGallery(strip_tags($_POST['name']), $_POST['photos']);
            $postId = $this->_createPost(strip_tags($_POST['name']), $_POST['report_content'], $galleryId);
            $this->_sendEmailNotification($postId);

            $response = array(
                'error' => false,
                'permalink' => get_permalink($postId)
            );
        } else {
            $response = array(
                'error' => true,
                'errors' => $validator->errors()
            );
        }
        echo json_encode($response);
        die();
    }

    private function _validator($data)
    {
        $v = new Validator($data);
        $v->rule('required', 'name')
          ->message('Заголовок звіту є обов\'язковим полем!');
        return $v;
    }

    private function _getNggFunctionsPath()
    {
        return WP_PLUGIN_DIR . '/nextgen-gallery/products/photocrati_nextgen/modules/ngglegacy/admin/functions.php';
    }

    private function _createGallery($name, $imageIds = null)
    {
        require_once $this->_getNggFunctionsPath();
        global $ngg;

        $name = 'Guest report ' . $name;
        $name = esc_attr($name);
        $defaultpath = $ngg->options['gallerypath'];
        $galleryId = nggAdmin::create_gallery($name, $defaultpath, false);

        if ($imageIds) {
            ob_start();
            $imageIds = array_map('intval', $imageIds);
            nggAdmin::move_images($imageIds, $galleryId);
            ob_get_clean();
        }

        return $galleryId;
    }

    private function _createPost($name, $content, $galleryId)
    {
        if ($galleryId) {
            $content .= "\n\n" . "[nggallery id={$galleryId}]";
            $content = $this->_updatePhotosGalleryPath($content, $galleryId);
        }
        $content = $this->_filterXSS($content);

        return wp_insert_post(array(
            'post_title'    => $name,
            'post_content'  => $content,
            'post_status'   => 'draft',
            'post_type'     => 'post'
        ));
    }

    private function _filterXSS($content)
    {
        // TODO filter all xss attributes and tags here
        return preg_replace('/script/i', '', $content);
    }

    private function _updatePhotosGalleryPath($content, $newGalleryId)
    {
        global $wpdb;
        $defaultGalleryId = get_option('npu_default_gallery');
        $defaultPath = $wpdb->get_var("SELECT path FROM $wpdb->nggallery WHERE gid = '$defaultGalleryId'");
        $newPath = $wpdb->get_var("SELECT path FROM $wpdb->nggallery WHERE gid = '$newGalleryId'");

        return str_replace($defaultPath, $newPath, $content);
    }

    private function _sendEmailNotification($postId)
    {
        $subject = '[Рибні місця Рівненщини] Додано звіт про рибалку';
        $message = 'Додано звіт про рибалку.' . "\n\n"
                 . 'Редагувати в базі:' . 'http://rivnefish.com/wp-admin/site_manager/markers/' . $postId . "\n"
                 . 'Дата:' . date("d M Y H:i:s") . "\n"
                 . 'IP: ' . $_SERVER['REMOTE_ADDR'] . "\n";
        $headers = 'From: ' . FROM_EMAIL;
        wp_mail(TO_EMAIL, $subject, $message, $headers);
    }
}

$fishMapPostReportPlugin = new FishMapPostReportPlugin();
