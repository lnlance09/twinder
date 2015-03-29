<?php
    $base_url = $this->config->base_url();

    // Define the tooltips
    $mr_tip = $mr_name.', '.$mr_age.' <br> <p>'.$mr_count.' matches</p>'; 
    $mrs_tip = $mrs_name.', '.$mrs_age.' <br> <p>'.$mrs_count.' matches</p>'; 
?>
<div id="chart_data" class="panel panel-default">
    <div class="panel-heading" id="glance">
        <h3 class="panel-title">
            <span class="stateface stateface-<?php echo strtolower($abbrev); ?>"></span> 
            <span id="state_header"><?php echo $state; ?></span> at a glance...
        </h3>
    </div>

    <div class="clearfix"></div>

    <!-- Draw the pie chart -->
    <div class="panel-body" id="chart_block">
        <div class="row">
            <div class="col-lg-4 col-sm-4">
                <canvas id="my_chart" class="pull-left" width="120" height="120"></canvas>

                <ul class="list-group pull-left">
                    <li class="list-group-item"><?php echo $total_count; ?> users</li>
                </ul>

                <div class="clearfix"></div>
            </div>

            <!-- The hottest guy -->
            <div class="col-lg-4 col-sm-4" id="mr">
<?php
    if(!empty($mr_name)) {
?>
                <a href="<?php echo $base_url.$mr_link; ?>" class="pull-left" data-toggle="tooltip" data-html="true" data-original-title="<?php echo $mr_tip; ?>">
                    <img src="<?php echo $mr_pic; ?>" class="img-circle" width="120" height="120" alt="<?php echo $mr_name; ?>">
                </a>
<?php
    } else {
?>
                <a href="<?php echo $base_url.$mr_link; ?>" class="pull-left">
                    <img src="<?php echo $base_url; ?>public/img/svg/kanye.svg" class="img-circle svg" width="120" height="120" alt="none" id="lance">
                </a>
<?php
    }
?>

                <div class="hottest_name pull-left">
                    <span class="salutation">Mr. <?php echo $state; ?></span><br>
<?php
    if(!empty($mr_name)) {
?>
                    <span><?php echo $mr_name.', '.$mr_age; ?></span><br>
                    <span><?php echo $mr_count; ?> matches</span>
<?php
    }
?>
                </div>

                <div class="clearfix"></div>
            </div>

            <!-- The hottest girl -->
            <div class="col-lg-4 col-sm-4" id="mrs">
<?php
    if(!empty($mrs_name)) {
?>
                <a href="<?php echo $base_url.$mrs_link; ?>" class="pull-left" data-toggle="tooltip" data-html="true" data-original-title="<?php echo $mrs_tip; ?>">
                    <img src="<?php echo $mrs_pic; ?>" class="img-circle" width="120" height="120" alt="<?php echo $mrs_name; ?>">
                </a>
<?php
    } else {
?>
                <a href="<?php echo $base_url.$mrs_link; ?>" class="pull-left">
                    <img src="<?php echo $base_url; ?>public/img/svg/mrs.svg" class="img-circle svg" width="120" height="120" alt="<?php echo $mrs_name; ?>">
                </a>
<?php
    }
?>
                <div class="hottest_name pull-left">
                    <span class="salutation">Mrs. <?php echo $state; ?></span><br>
<?php
    if(!empty($mr_name)) {
?>
                    <span><?php echo $mrs_name.', '.$mrs_age; ?></span><br>
                    <span><?php echo $mrs_count; ?> matches</span>
<?php
    }
?>
                </div>

                <div class="clearfix"></div>
            </div>
        </div>

        <div class="clearfix"></div>
    </div>
</div>

<script>
    var data = [
        {
            value: parseInt(<?php echo $male_count; ?>),
            color: '#ad5',
            highlight: '#aa3',
            label: 'Women'
        },
        {
            value: parseInt(<?php echo $female_count; ?>),
            color: '#0993c7',
            highlight: '#3090cc',
            label: 'Men'
        }
    ];

    var options = {
                segmentShowStroke : true,
                segmentStrokeColor : '#fff',
                segmentStrokeWidth : 2,
                percentageInnerCutout : 0, 
                animationSteps : 100,
                animationEasing : 'easeOutBounce',
                animateRotate : true,
                animateScale : false,
                legendTemplate : false
            }
    var ctx = $('#my_chart').get(0).getContext('2d');
    var pie = new Chart(ctx).Pie(data, options);

    jQuery('.svg').each(function() {
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
</script>