<?php

class GalleryModel
{
    public function createGallery($name, $imageIds = null)
    {
        require_once $this->_getNggFunctionsPath();
        global $ngg;

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

    public function updatePhotosPath($content, $newGalleryId)
    {
        global $wpdb;
        $oldGalleryId = get_option('npu_default_gallery');
        $oldPath = $wpdb->get_var("SELECT path FROM $wpdb->nggallery WHERE gid = '$oldGalleryId'");
        $newPath = $wpdb->get_var("SELECT path FROM $wpdb->nggallery WHERE gid = '$newGalleryId'");

        return str_replace($oldPath, $newPath, $content);
    }

    private function _getNggFunctionsPath()
    {
        return WP_PLUGIN_DIR . '/nextgen-gallery/products/photocrati_nextgen/modules/ngglegacy/admin/functions.php';
    }
}
