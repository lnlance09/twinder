<?php
	header('Content-type: application/xml');
	echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>http://twinder.io/</loc> 
        <changefreq>hourly</changefreq>
        <priority>1.0</priority>
    </url>

	<url>
		<loc>http://twinder.io/signin</loc>
		<changefreq>never</changefreq>
		<priority>0.8</priority>
	</url>

<?php 
	for($i=0;$i<count($users);$i++) { 
?>
    <url>
        <loc><?php echo 'http://twinder.io/'.$users[$i]['link']; ?></loc>
        <changefreq>daily</changefreq>
        <priority>0.7</priority>
    </url>
<?php 
	} 

	for($i=0;$i<count($matches);$i++) {
?>
	<url>
        <loc><?php echo 'http://twinder.io/matches/'.$matches[$i]['match_id']; ?></loc>
        <changefreq>daily</changefreq>
        <priority>0.7</priority>
    </url>
<?php
	}

	/*
	for($i=0;$i<count($places);$i++) {
?>
	<url>
        <loc><?= 'http://twinder.io/hot/'.$places[$i]['city'].'/'.$$places[$i]['state']; ?></loc>
        <changefreq>daily</changefreq>
        <priority>0.7</priority>
    </url>
<?php
	}
	*/
?>

    <url>
		<loc>http://twinder.io/about</loc>
		<changefreq>never</changefreq>
		<priority>0.4</priority>
	</url>

	<url>
		<loc>http://twinder.io/terms</loc>
		<changefreq>never</changefreq>
		<priority>0.4</priority>
	</url>

	<url>
		<loc>http://twinder.io/faq</loc>
		<changefreq>never</changefreq>
		<priority>0.4</priority>
	</url>

	<url>
		<loc>http://twinder.io/contact</loc>
		<changefreq>never</changefreq>
		<priority>0.4</priority>
	</url>
</urlset>