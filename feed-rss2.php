<?php
/**
 * Flux RSS 2.0 personnalisé – LaboMaison
 * Basé sur la structure WordPress standard, avec :
 *  - Image <media:content> en taille "full" (>=1200 px)
 *  - Ajout <language> fr-FR et <sy:updatePeriod>
 *  - Ajout des <category>
 *  - Nettoyage du <description>
 */

header('Content-Type: application/rss+xml; charset=' . get_option('blog_charset'), true);
echo '<?xml version="1.0" encoding="' . get_option('blog_charset') . '"?' . '>';
?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	xmlns:media="http://search.yahoo.com/mrss/"
>
<channel>
	<title><?php bloginfo_rss('name'); ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php bloginfo_rss('url') ?></link>
	<description><?php bloginfo_rss('description') ?></description>
	<language>fr-FR</language>
	<lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
	<sy:updatePeriod>hourly</sy:updatePeriod>
	<sy:updateFrequency>1</sy:updateFrequency>

<?php
while (have_posts()) : the_post();
	$post_id = get_the_ID();
	$thumb_id = get_post_thumbnail_id($post_id);
	$image_url = '';
	$image_w = '';
	$image_h = '';

	if ($thumb_id) {
		$img = wp_get_attachment_image_src($thumb_id, 'full'); // toujours la version originale
		if ($img) {
			$image_url = esc_url($img[0]);
			$image_w = intval($img[1]);
			$image_h = intval($img[2]);
		}
	}
?>
	<item>
		<title><?php the_title_rss(); ?></title>
		<link><?php the_permalink_rss(); ?></link>
		<guid isPermaLink="false"><?php the_guid(); ?></guid>
		<pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
		<dc:creator><![CDATA[<?php the_author(); ?>]]></dc:creator>

		<?php
		// Ajouter les catégories de l’article
		$cats = get_the_category($post_id);
		if ($cats) {
			foreach ($cats as $cat) {
				echo '<category>' . esc_html($cat->name) . '</category>' . "\n";
			}
		}
		?>

		<?php if ($image_url) : ?>
			<media:content 
				url="<?php echo $image_url; ?>" 
				medium="image" 
				width="<?php echo $image_w; ?>" 
				height="<?php echo $image_h; ?>" 
			/>
		<?php endif; ?>

		<description><![CDATA[
			<?php
			$excerpt = get_the_excerpt();
			if (empty($excerpt)) {
				$excerpt = wp_strip_all_tags(get_the_content());
			}
			echo wp_trim_words($excerpt, 40, '…');
			?>
		]]></description>

		<content:encoded><![CDATA[<?php the_content_feed('rss2'); ?>]]></content:encoded>
	</item>

<?php endwhile; ?>
</channel>
</rss>
