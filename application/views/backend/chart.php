<?php
    $base_url = $this->config->base_url();

    if($female_count == 0) {
        $female_count = 1;
    }

    if($male_count == 0) {
        $male_count = 1;
    }
?>
<div id="chart_data">
    <div class="col-lg-12" id="glance">
        <h2>
            <span class="stateface stateface-<?php echo strtolower($abbrev); ?>"></span> 
            <span id="state_header"><?php echo $state; ?></span> at a glance...
        </h2>
    </div>

    <div class="clearfix"></div>

    <!-- Draw the pie chart -->
    <div class="col-lg-12" id="chart_block">
        <div class="col-lg-4">
            <canvas id="my_chart" class="pull-left" width="120" height="120"></canvas>

            <ul class="list-group pull-left">
                <li class="list-group-item"><span><?php echo $total_count; ?></span> users</li>
                <li class="list-group-item"><span><?php echo $state_rank; ?></span> most populous state</li>
                <li class="list-group-item"><span><?php echo $avg; ?></span> average age</li>
            </ul>

            <div class="clearfix"></div>
        </div>

        <!-- The hottest guy -->
        <div class="col-lg-4" id="mr">
            <a href="<?php echo $base_url.$mr_link; ?>" class="pull-left">
                <img src="<?php echo $mr_pic; ?>" class="img-circle" width="120" height="120" alt="<?php echo $mr_name; ?>">
            </a>

            <div class="hottest_name pull-left">
                <span><?php echo $mr_name; ?></span>
            </div>

            <div class="clearfix"></div>
        </div>

        <!-- The hottest girl -->
        <div class="col-lg-4" id="mrs">
            <a href="<?php echo $base_url.$mrs_link; ?>" class="pull-left">
                <img src="<?php echo $mrs_pic; ?>" class="img-circle" width="120" height="120" alt="<?php echo $mrs_name; ?>">
            </a>

            <div class="hottest_name pull-left">
                <span><?php echo $mrs_name; ?></span>
            </div>

            <div class="clearfix"></div>
        </div>
    </div>

    <div class="clearfix"></div>
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
            value: parseInt(<?php echo $male_count; ?>),
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
</script>