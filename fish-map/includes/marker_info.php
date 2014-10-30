<?php

class MarkerInfo
{
    private $_markerModel;
    private $_fishModel;

    public function __construct()
    {
        $this->_markerModel = new MarkerModel();
        $this->_fishModel = new FishModel();
    }


    public function getInfo($markerId)
    {
        $markerRow = $this->_markerModel->getById($markerId);
        return $this->getRowInfo($markerRow);
    }

    public function getRowInfo($markerRow)
    {
        $markerRow['page_url'] = $this->_getPageUrl($markerRow);
        $markerRow['photos'] = $this->_getPhotos($markerRow);
        $markerRow['fishes'] = $this->_fishModel->getByMarker($markerRow['marker_id']);
        return $markerRow;
    }

    private function _getPageUrl($row)
    {
        $pageUrl = $this->_markerModel->getPageUrl($row);
        return $pageUrl ? $pageUrl : '';
    }

    private function _getPhotos($row)
    {
        global $nggdb;
        $photos = array();
        if ($row['gallery_id']) {
            $gallery = $nggdb->get_gallery($row['gallery_id'], 'sortorder', 'ASC', true, 4);
            foreach ($gallery as $image) {
                $photos[] = array(
                    'thumbnail' => $image->thumbURL,
                    'photo' => $image->imageURL
                );
            }
        }
        return $photos;
    }
} 