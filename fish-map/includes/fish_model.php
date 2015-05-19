<?php

class FishModel
{
    public function __construct()
    {
        global $wpdb;
        return $this->db = $wpdb;
    }

    public function getIds()
    {
        return $this->db->get_col("SELECT fish_id FROM fishes");
    }

    public function getNames()
    {
        return $this->db->get_results("SELECT fish_id, name FROM fishes ORDER BY name");
    }

    public function getAll()
    {
        return $this->db->get_results("SELECT * FROM fishes ORDER BY name");
    }

    public function getByMarker($markerId)
    {
        $query_fish = $this->db->prepare(
            "SELECT *
             FROM markers_fishes mf
             INNER JOIN fishes f on f.fish_id = mf.fish_id
             WHERE mf.marker_id = %d
             ORDER BY name ASC", $markerId);

        return $this->db->get_results($query_fish, ARRAY_A);
    }

    public function insertMarkerFishes($markerId, $fishes)
    {
        $fishIds = $this->getIds();
        foreach ($fishes as $fishId => $data) {
            $fishId = intval($fishId);
            if (in_array($fishId, $fishIds)) {
                $this->db->insert('markers_fishes', array(
                    'marker_id' => $markerId,
                    'fish_id' => $fishId,
                    'amount' => $this->_amount($data['amount']),
                    'weight_avg' => intval($data['weight_avg']),
                    'weight_max' => intval($data['weight_max']),
                    'notes' => $data['notes']
                ));
            }
        }
    }

    private function _amount($amount) {
        return empty($amount) ? null : max(min(10, intval($amount)), 1);
    }
    
}