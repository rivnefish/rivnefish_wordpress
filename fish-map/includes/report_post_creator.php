<?php

require_once 'Valitron/Validator.php';
require_once 'htmLawed.php';
use Valitron\Validator;
Validator::langDir(__DIR__ . '/Valitron');

class ReportPostCreator
{
    public $validator;
    private $_data;
    private $_galleryModel;

    public function __construct($data)
    {
        $this->_data = $data;
        $this->_data['name'] = strip_tags($data['name']);
        $this->validator = $this->_createValidator();
        $this->_galleryModel = new GalleryModel();
    }

    public function createReportPost()
    {
        $galleryId = null;
        if (isset($this->_data['photos'])) {
            $galleryName = 'Guest report ' . $this->_data['name'];
            $galleryId = $this->_galleryModel->createGallery($galleryName, $this->_data['photos']);
        }
        $postId = $this->_createPost($galleryId);
        return $postId;
    }

    private function _createPost($galleryId)
    {
        $content = $this->_filterXSS($this->_data['report_content']);
        if ($galleryId) {
            $content = $this->_galleryModel->updatePhotosPath($content, $galleryId);
            $content .= "\n\n<h3>Всі фото з рибалки:</h3>";
            $content .= "\n" . "[nggallery id={$galleryId}]";
        }

        return wp_insert_post(array(
            'post_title'    => $this->_data['name'],
            'post_content'  => $content,
            'post_status'   => 'draft',
            'post_type'     => 'post'
        ));
    }

    private function _filterXSS($content)
    {
        return htmLawed($content, array('safe' => 1));
    }

    private function _createValidator()
    {
        $v = new Validator($this->_data);
        $v->rule('required', 'name')
          ->message('Заголовок звіту є обов\'язковим полем!');
        return $v;
    }
}
