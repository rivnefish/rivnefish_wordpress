<?php
class PostInfo
{
    public function __construct($postId)
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->postId = $postId;
    }

    public function getPostTaxonomy()
    {
        $sql = "SELECT name, slug, taxonomy, description
                FROM wp_term_relationships tr
                JOIN wp_term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                JOIN wp_terms t ON t.term_id = tt.term_id
                WHERE object_id = %d";
        $query_taxonomy = $this->db->prepare($sql, $this->postId);
        return $this->db->get_results($query_taxonomy, ARRAY_A);
    }

    public function getPostInfo()
    {
        $marker_post = get_post($this->postId, ARRAY_A);
        $marker_post['rendered_content'] = get_post_content_by_id($this->postId);
        $marker_post['featured_image'] = wp_get_attachment_url( get_post_thumbnail_id($this->postId) );
        $marker_post['_yoast_wpseo_title'] = get_post_meta($this->postId, '_yoast_wpseo_title', true);
        $marker_post['_yoast_wpseo_metadesc'] = get_post_meta($this->postId, '_yoast_wpseo_metadesc', true);
        $marker_post['taxonomy'] = $this->getPostTaxonomy();
        return $marker_post;
    }

    public static function getPostIds()
    {
        global $wpdb;
        return $wpdb->get_col("SELECT ID FROM wp_posts where post_type = 'post' and post_status = 'publish'");
    }
}
