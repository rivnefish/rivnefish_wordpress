<?php
/*
YARPP Related Posts with pictures by stal1n
*/
?>
<p>related posts:</p>
<?php if ($related_query->have_posts()):?>
<table>
<tr>
<?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
<?php
$post_pictures = get_post_custom_values('post-picture');
$post_picture = $post_pictures[0];
if ($post_picture == '')
{
$post_picture = 'https://lh6.googleusercontent.com/-uKLGG4kw5jk/TtqwhPErXtI/AAAAAAAAD2M/OrQajRTjn2I/s100/carp9000.png';
}
?>
<td width="20%">
<a href="<?php the_permalink() ?>" rel="bookmark">
<img src="<?php echo $post_picture; ?>" title="<?php the_title(); ?>" alt="<?php the_title(); ?>"/>
<br /><?php the_title(); ?>
</a>
</td>
<?php endwhile; ?>
</tr>
</table>
<?php else: ?>
<p>nothing found</p>
<?php endif; ?>