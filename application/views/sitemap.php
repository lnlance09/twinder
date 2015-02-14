<?php
	$base_url = $this->config->base_url();

	header('Content-type: application/xml');
	echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?php echo $base_url; ?></loc> 
        <changefreq>never</changefreq>
        <priority>1.0</priority>
    </url>

	<url>
		<loc><?php echo $base_url; ?>hot</loc>
		<changefreq>never</changefreq>
		<priority>0.9</priority>
	</url>

	<url>
		<loc><?php echo $base_url; ?>signin</loc>
		<changefreq>never</changefreq>
		<priority>0.8</priority>
	</url>

    <url>
		<loc><?php echo $base_url; ?>about</loc>
		<changefreq>never</changefreq>
		<priority>0.4</priority>
	</url>

	<url>
		<loc><?php echo $base_url; ?>terms</loc>
		<changefreq>never</changefreq>
		<priority>0.4</priority>
	</url>

	<url>
		<loc><?php echo $base_url; ?>faq</loc>
		<changefreq>never</changefreq>
		<priority>0.4</priority>
	</url>

	<url>
		<loc><?php echo $base_url; ?>contact</loc>
		<changefreq>never</changefreq>
		<priority>0.4</priority>
	</url>
<?php 
	for($i=0;$i<count($links);$i++) { 
?>
    <url>
        <loc><?= $base_url.$links[$i]; ?></loc>
        <changefreq>never</changefreq>
        <priority>0.7</priority>
    </url>
<?php 
	} 
?>
</urlset>