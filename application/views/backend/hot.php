<?php
	$base_url = $this->config->base_url();
?>
    <!-- Write the page number for the JS to work -->
    <div class="hidden" id="load_page"><?php echo $page; ?></div>

    <div class="sub_load">
<?php
    if($count > 0) {
?>
        <div id="media_container">
<?php
    	for($i=0;$i<$end;$i++) {
            $user = $hot['users'][$i];

            // Show the highligthed text
            $high = (!empty($q) ? str_replace($q, '<span class="highlight">'.$q.'</span>', $user['bio']) : $user['bio']);
?>
            <div class="media" onclick="location.href='<?php echo $base_url.$user['link']; ?>'">
                <div class="media-left media-top">
                    <a href="<?php echo $base_url.$user['link']; ?>">
                        <img src="<?php echo $user['profile_pic']; ?>" class="media-object img-circle" alt="<?php echo $user['name']; ?>">
                    </a>
                </div>
                
                <div class="media-body text-left">
                    <h4 class="media-heading">
                        <a href="<?php echo $base_url.$user['link']; ?>" title="<?php echo $user['name']; ?>"><?php echo $user['name']; ?></a>, <?php echo $user['age']; ?>
                    </h4>

                    <p>
                        <?php echo BioLinks($high); ?>
                    </p>
                </div>
            </div>
<?php
        }
?>
        </div>

        <div id="append"></div>
<?php
    }
?>
    </div>
<?php
    if($count == 0) {
?>
    <div class="main_none">
        <img class="svg" id="ghost" src="<?php echo $base_url; ?>public/img/svg/snowden.svg" width="150" height="150" alt="no results">

        <p>
            Sorry, try these places instead...
        </p>
    </div>

    <ul class="list-group text-left">
<?php
    for($i=0;$i<$places['count'];$i++) {
        $place = $places['places'][$i];
?>
        <li class="list-group-item" onclick="location.href='<?php echo $base_url.'hot/gender/both/state/'.$place['state'].'/city/'.$place['city']; ?>'">
            <a href="#"><img src="<?php echo $base_url.'public/img/flags/'.$place['flag']; ?>.png" width="24" alt="<?php echo $place['state']; ?>"> <?php echo $place['city']; ?></a>

            <span class="miles pull-right">
                <?php echo number_format($place['distance']); ?> miles
            </span>

            <span class="clearfix"></span>
        </li>
<?php
    }
?>
        </ul>
    
<?php
    } else {
        if(($page+1) < $pages) {
?>
    <div class="text-center">
        <button type="button" class="btn btn-default" id="see_more">see more (<?php echo number_format($left_over); ?>)</button>
    </div>
<?php
        }
    }
?>
    <script>
        var base_url = '<?php echo $base_url; ?>';

        function GetFullURL() {
            var str;
            var params = {
                        gender: $('[name="gender"]'), 
                        lat: $('#drag_lat'), 
                        lon: $('#drag_lon'), 
                        distance: $('#distance-value'), 
                        min: $('#lower-value'), 
                        max: $('#upper-value'), 
                        page: $('#page')
                    };

            for(var index in params) {
                switch(index) {
                    case'lon':

                        var val = params[index].text();
                        if(val == '' || val == 2.169919) {
                            var val = 'all';
                        }
                        break;

                    case'lat':

                        var val = params[index].text();
                        if(val == '' || val == 41.387917) {
                            var val = 'all';
                        }
                        break;

                    case'gender':

                        var val = params[index].text().trim().toLowerCase();
                        if(val === undefined || val == '') {
                            var val = 'both';
                        }
                        break;

                    case'page':

                        var val = parseInt(params[index].text().trim()) + parseInt(1);
                        break;

                    default:
                        var val = params[index].text().trim();
                }

                str += index +'/'+ val +'/';
            }

            var q = $('#users_autocomplete').val();
            return str.substr(9, str.length-10) +'?q='+ q;
        }

        function ChangeTitleURL() {
            var new_url = base_url +'hot/'+ GetFullURL();
            window.history.replaceState('', '', new_url);
        }

        $('button#see_more').click(function(e) {
            $('#append').html('<div class="ajax-loader"><i class="fa fa-circle-o-notch fa-4x fa-spin"></i></div>');
            var data = '<?php echo $query; ?>&page=<?php echo $new_page; ?>';
            console.log(data);

            $('#hot_load').load(base_url +'hot/GetHottest', data, function() {
                $('#hot_load .ajax-loader').fadeOut();
                ChangeTitleURL();
            });
        });

        $('#hot_count_num').text('<?php echo FormatNumber($count); ?>');
<?php
    if($count == 0) {
?>
        // SVG script
        jQuery('img.svg').each(function() {
            var $img = jQuery(this);
            var imgID = $img.attr('id');
            var imgClass = $img.attr('class');
            var imgURL = $img.attr('src');

            jQuery.get(imgURL, function(data) {
                // Get the SVG tag, ignore the rest
                var $svg = jQuery(data).find('svg');

                // Add replaced image's ID to the new SVG
                if(typeof imgID !== 'undefined') {
                    $svg = $svg.attr('id', imgID);
                }
                // Add replaced image's classes to the new SVG
                if(typeof imgClass !== 'undefined') {
                    $svg = $svg.attr('class', imgClass +' replaced-svg');
                }

                // Remove any invalid XML tags as per http://validator.w3.org
                $svg = $svg.removeAttr('xmlns:a');

                // Replace image with new SVG
                $img.replaceWith($svg);
            }, 'xml');
        });
<?php
    }
?>
    </script>