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
        $fields = array('marker_id', 'name', 'paid_fish', 'contact', 'contact_name', 'photo_url1', 'photo_url2',
                        'photo_url3', 'photo_url4');

        if (is_export_info_request()) {
            $rowInfo = $markerRow;
            $rowInfo['photos_all'] = $this->_getPhotos($markerRow, 100);
        } else {
            $rowInfo = array();
            foreach ($fields as $field) {
                $rowInfo[$field] = $markerRow[$field];
            }
        }
        $rowInfo['page_url'] = $this->_getPageUrl($markerRow);
        $rowInfo['photos'] = $this->_getPhotos($markerRow, 4);
        $rowInfo['fishes'] = $this->_fishModel->getByMarker($markerRow['marker_id']);
        return $rowInfo;
    }

    private function _getPageUrl($row)
    {
        $pageUrl = $this->_markerModel->getPageUrl($row);
        return $pageUrl ? $pageUrl : '';
    }

    private function _getPhotos($row, $limit)
    {
        global $nggdb;
        $photos = array();
        if ($row['gallery_id']) {
            $gallery = $nggdb->get_gallery($row['gallery_id'], 'sortorder', 'ASC', true, $limit);
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