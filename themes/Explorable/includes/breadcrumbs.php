<div id="breadcrumbs">
	<?php if(function_exists('bcn_display')) { bcn_display(); }
		  else { ?>
				<a href="<?php echo esc_url( home_url() ); ?>" class="breadcrumbs_home"><?php esc_html_e('Home','Explorable') ?></a> <span class="raquo">&raquo;</span>

				<?php if( is_tag() ) { ?>
					<?php esc_html_e('Posts Tagged ','Explorable') ?><span class="raquo">&quot;</span><?php single_tag_title(); echo('&quot;'); ?>
				<?php } elseif (is_day()) { ?>
					<?php esc_html_e('Posts made in','Explorable') ?> <?php the_time('F jS, Y'); ?>
				<?php } elseif (is_month()) { ?>
					<?php esc_html_e('Posts made in','Explorable') ?> <?php the_time('F, Y'); ?>
				<?php } elseif (is_year()) { ?>
					<?php esc_html_e('Posts made in','Explorable') ?> <?php the_time('Y'); ?>
				<?php } elseif (is_search()) { ?>
					<?php esc_html_e('Search results for','Explorable') ?> <?php the_search_query() ?>
				<?php } elseif (is_single()) { ?>
				<?php
					if ( 'listing' == get_post_type() ) {
						$categories = get_the_terms( get_the_ID(), 'listing_type' );
						if ( $categories ) {
							foreach( $categories as $category ) {
								$catlink = get_term_link( $category );
								echo ('<a href="'.esc_url( $catlink ).'">'.esc_html($category->name).'</a> '.'<span class="raquo">&raquo;</span> ');
								break;
							}
						}
					} else {
						$category = get_the_category();
						if ( $category ) {
							$catlink = get_category_link( $category[0]->cat_ID );
							echo ('<a href="'.esc_url($catlink).'">'.esc_html($category[0]->cat_name).'</a> '.'<span class="raquo">&raquo;</span> ');
						}
					}

					echo get_the_title();
				?>
				<?php } elseif (is_category()) { ?>
					<?php single_cat_title(); ?>
				<?php } elseif (is_tax()) { ?>
					<?php
						$et_taxonomy_links = array();
						$et_term = get_queried_object();
						$et_term_parent_id = $et_term->parent;
						$et_term_taxonomy = $et_term->taxonomy;

						while ( $et_term_parent_id ) {
							$et_current_term = get_term( $et_term_parent_id, $et_term_taxonomy );
							$et_taxonomy_links[] = '<a href="' . esc_url( get_term_link( $et_current_term, $et_term_taxonomy ) ) . '" title="' . esc_attr( $et_current_term->name ) . '">' . esc_html( $et_current_term->name ) . '</a>';
							$et_term_parent_id = $et_current_term->parent;
						}

						if ( !empty( $et_taxonomy_links ) ) echo implode( ' <span class="raquo">&raquo;</span> ', array_reverse( $et_taxonomy_links ) ) . ' <span class="raquo">&raquo;</span> ';

						echo esc_html( $et_term->name );
					?>
				<?php } elseif (is_author()) { ?>
					<?php
						global $wp_query;
						$curauth = $wp_query->get_queried_object();
					?>
					<?php esc_html_e('Posts by ','Explorable'); echo ' ',$curauth->nickname; ?>
				<?php } elseif (is_page()) { ?>
					<?php wp_title(''); ?>
				<?php }; ?>
	<?php } ?>

	<span class="raquo">&raquo;</span>
</div> <!-- #breadcrumbs -->