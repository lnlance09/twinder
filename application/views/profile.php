 <?php
    $base_url = $this->config->base_url();
    // FormatArray($user_info);
    $img_path = 'http://images.gotinder.com/'.$user_info['tinder_id'].'/'; 
?>
        <div id="header-section">
            <div class="container" id="profile_top">
                <div id="single_users_load">
                	
                	<div class="col-lg-12" id="focus_box">
		                <!-- Ping the user's last location -->
		                <div id="ping_wrapper">
		                    <div id="ping_map"></div>

		                    <div class="infobox-wrapper">
		                    	<div id="infobox">
		                    		<h2>Last seen by</h2>

		                    		<?php echo $last_seen; ?>
		                    	</div>

		                    	<div class="clearfix"></div>
		                    </div>
		                </div>

		                <div id="profile_page_info">
		                    <div class="col-lg-4">
		                        <div id="polaroid_wrap">
		                            <img src="<?php echo $img_path.'172x172_'.$user_info['profile_pic']; ?>" width="200" height="200" alt="<?php echo $user_info['name']; ?>" id="main_img" />
		                        </div>
		                    </div>

		                    <form method="POST" action="<?php echo $base_url; ?>users/EditProfile" id="edit_profile">
		                        <div class="col-lg-8" id="profile_info">
		                            <h1 class="static">
<?php 
    echo $user_info['name'].', '.$user_info['age'];

    if($edit === TRUE) {
?>
		                                <button class="btn btn-default pull-right" type="button" id="click_to_edit">Edit</button>
		                                <span class="clearfix"></span>
<?php
	}
?>
		                            </h1>
		                    
		                            <div id="about_quote">
		                                <span>
		                                    <?php echo $user_info['bio']; ?>
		                                </span>
		                            </div>
		                        </div>

		                        <div class="clearfix"></div>
		                    </form>
		                </div>

		                <ul id="sub_pics" class="polaroid-images">
<?php
	// Loop thru the pics
    $pic_count = count($user_info['pics']['file']);

    if($pic_count >= 5) {
        $end = 5;
    } else {
        $end = $pic_count;
    }

    for($i=0;$i<$end;$i++) {
?>
		                    <li name="<?php echo $img_path.'172x172_'.$user_info['pics']['file'][$i]; ?>">
		                        <a href="#">
		                            <img src="<?php echo $img_path.'84x84_'.$user_info['pics']['file'][$i]; ?>" width="100" height="100" class="" alt="<?php echo $user_info['name']; ?>" />
		                        </a>
		                    </li>
<?php
    }
?>
		                </ul>
		            </div>

		            <div class="col-lg-12 text-center" id="below_box">
<?php
	// Loop thru all of the stats
	for($i=0;$i<count($stats['results']);$i++) {
		if($i == 0) {
			$id = 'active';
		} else {
			$id = '';
		}
?>
		                <div class="col-lg-2 timer_box" id="<?php echo $id; ?>" name="<?php echo $stats['results'][$i]['name']; ?>">
		                	<div class="inside">
		                    	<h3><?php echo strtoupper($stats['results'][$i]['name']); ?></h3>
		                    	<p><?php echo $stats['results'][$i]['count']; ?></p>
							</div>

		                    <div class="border_active"></div>
		                </div>
<?php
	}
?>

		                <div class="clearfix"></div>

		                <div class="input-group" id="search_wrapper">
		                    <div class="input-group-addon">
		                    	<i class="fa fa-heart fa-lg" id="fa_type"></i>
		                    </div>
		                    
		                    <input type="text" class="form-control" placeholder="Search matches" id="search_connections" autocomplete="off">
		                </div>
		            </div>

		            <div class="clearfix"></div>

		            <div id="connections_box">
		                <div id="con_load_box">
		                    <div class="ajax-loader">
		                        <i class="fa fa-refresh fa-2x fa-spin"></i>
		                    </div>
		                </div>

		                <div class="hidden" id="type_name">matches</div>
		            </div>
                </div>
<?php
    // If the user is logged in and they aren't viewing their own profile, then display a link to report them along with a modal
    if($report === TRUE) {
?>
				<div class="col-lg-12" id="fb_info_box">
					<div class="col-lg-6">
						<h4>
							<?php echo $user_info['fb_friend_count']; ?> mutual friends
						</h4>
					</div>

					<div class="col-lg-6">
						<h4>
							<?php echo $user_info['fb_like_count']; ?> mutual likes
						</h4>
					</div>

					<div class="clearfix"></div>
                </div>


	            <a href="#" id="report_user" data-toggle="modal" data-target="#report_modal"><i class="fa fa-send-o"></i> Report <?php echo $user_info['name']; ?></a>

	            <div id="report_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	                <div class="modal-dialog">
	                    <div class="modal-content">
	                    	<form id="report_form" method="POST">
		                    	<div class="modal-header">
		                    		<h3>
		                    			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
										<i class="fa fa-bullhorn fa-md"></i> Report <?php echo $user_info['name']; ?>
									</h3>
		                    	</div>

		                        <div class="modal-body">
		                        	<ul class="list-group">
			                            <li class="list-group-item" name="1">feels like spam</li>
			                            <li class="list-group-item" name="2">inappropriate or offensive</li>
			                            <li class="list-group-item" name="0" id="other_trigger">other...</li>
		                        	</ul>

									<div id="other_box">
		                        		<textarea class="form-control" placeholder="Why are you reporting this?" id="other_comment"></textarea><br>

		                        		<button class="btn btn-primary pull-right" type="button" id="report_text">Submit</button>

		                        		<div class="clearfix"></div>
		                        	</div>
		                        </div>
		                    </form>
	                    </div>
	                </div>
	            </div>
<?php
    }
?>
            </div>

            <div class="hidden" id="user_tinder_id"><?php echo $user_info['tinder_id']; ?></div>
            <div class="hidden" id="can_edit"><?php echo $edit; ?></div>
            <div class="hidden" id="lon"><?php echo $lon; ?></div>
            <div class="hidden" id="lat"><?php echo $lat; ?></div>
            <div class="hidden" id="radius"><?php echo $distance; ?></div>
        </div>
