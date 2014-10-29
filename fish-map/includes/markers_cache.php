<?php

class MarkersCache
{
    private $_infoModel;

    public function __construct()
    {
        $this->_infoModel = new MarkerInfo();
    }

    public function getMarkerInfo($row)
    {
        $key = $this->_infoCacheKey($row);
        $info = wp_cache_get($key);
        if ($info === false) {
            $info = $this->_infoModel->getInfo($row['marker_id']);
            wp_cache_set($key, $info);
        }
        return $info;
    }

    private function _infoCacheKey($row)
    {
        return 'marker_info_' . $row['marker_id'] . '_' . $row['modify_date'];
    }
} 