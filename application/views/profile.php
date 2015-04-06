 <?php
    $base_url = $this->config->base_url();
    // FormatArray($user_info);
?>
    <div id="profile-section">
        <div class="container">
            <div id="single_users_load">
            	<div id="focus_box">
	                <!-- Ping the user's last location -->
	                <div id="ping_wrapper">
	                    <div id="ping_map"></div>

						<!-- Infobox for Google Maps -->
                    	<div id="infobox">
                    		<?php echo $last_seen; ?>
                    	</div>
	                </div>

	                <div class="clearfix"></div>

					<!-- Stripe -->
					<div id="stripe">
						<div class="col-lg-8 pull-right">
							<div class="row">
<?php
	for($i=0;$i<count($stats);$i++) {
		$id = ($stats[$i]['name'] == $tab_active ? 'active' : '');
?>
				                <div class="col-lg-2 timer_box" id="<?php echo $id; ?>" name="<?php echo $stats[$i]['name']; ?>">
				                	<div class="inside">
				                    	<h3>
				                    		<?php echo strtoupper($stats[$i]['name']); ?>
				                    	</h3>

				                    	<p><?php echo $stats[$i]['count']; ?></p>
									</div>

				                    <div class="border_active"></div>
				                </div>
<?php
	}
?>
							</div>
<?php
	// Display the edit button
    if($edit) {
?>
                    		<button class="btn btn-primary" type="button" id="click_to_edit">Edit</button>
<?php
	} 

	if($like['perm']) {
		if($like['perm'] == 'liked') {
?>
							<button class="btn btn-success" type="button">Liked!</button>
<?php
		} elseif($like['perm'] == 'matched') {
?>
							<button class="btn btn-warning" type="button" id="unmatch_user">Matched</button>
<?php
		} elseif($like['perm'] == 'can_like') {
?>
							<button class="btn btn-default" type="button" id="like_user"><i class="fa fa-heart"></i> Like</button>
<?php
		}
	}
?>
						</div>
			
						<div class="clearfix"></div>
					</div>						

	                <div id="profile_page_info">
	                	<!-- Profile Pic -->
	                    <div class="col-lg-3">
	                    	<a href="#" class="thumbnail">
	                        	<img src="<?php echo $user_info['profile_pic']; ?>" alt="<?php echo $user_info['name']; ?>" id="main_img">
	                        </a>
	                        
		                    <form method="POST" action="<?php echo $base_url; ?>users/EditProfile" id="edit_profile">
		                    	<!-- Name and age of user -->
		                        <h1 class="static">
									<?php echo $user_info['name'].', '.$user_info['age']; ?>
	                            </h1>
	                    
<?php
	// Display the username is necessary
	if(!empty($user_info['username'])) {
?>
								<p>@<?php echo $user_info['username']; ?></p>
<?php
	}
?>
	                    		<!-- Bio -->
	                            <div id="about_quote">
	                                <?php echo $user_info['bio']; ?>
	                            </div>

								<!-- Bio Edit -->
	                            <textarea id="bio_text" class="form-control"><?php echo $user_info['bio']; ?></textarea>

								<ul id="user_info">
									<!-- City and state -->
									<li><i class="fa fa-map-marker fa-fw"></i> Last seen near <a href="<?php echo $base_url.'hot/gender/both/city/'.$city.'/state/'.$state.'/'; ?>"><?php echo $city.', '.$state; ?></a></li>
									<li><i class="fa fa-clock-o fa-fw"></i> Last active <?php echo FormatTime($user_info['last_activity_date']); ?></li>
<?php
	if($twitter['access'] == 'true') {
?>
									<li><i class="fa fa-twitter fa-fw"></i> <a href="https://twitter.com/<?php echo $twitter['handle']; ?>" target="_blank"><?php echo $twitter['handle']; ?></a></li>
<?php
	}
?>
									<li><i class="fa fa-camera-retro fa-fw"></i> <?php echo $pic_count; ?> photos</li>
								</ul>

								<!-- The edit, like and unmatch buttons for mobile display -->
								<div id="resize_edit">
<?php
    if($edit) {
?>
                    				<button class="btn btn-primary" type="button" id="resize_click_to_edit">Edit</button>
<?php
	} 

	if($like['perm']) {
		if($like['perm'] == 'liked') {
?>
									<button class="btn btn-success" type="button">Liked!</button>
<?php
		} elseif($like['perm'] == 'matched') {
?>
									<button class="btn btn-warning" type="button" id="resize_unmatch_user">Matched</button>
<?php
		} elseif($like['perm'] == 'can_like') {
?>
									<button class="btn btn-default" type="button" id="resize_like_user"><i class="fa fa-heart"></i> Like</button>
<?php
		}
	}
?>
								</div>
		                    </form>

	                		<ul id="sub_pics">
<?php
	// Loop thru the pics
    for($i=0;$i<$pic_count;$i++) {
    	$size_one = ChangePicSize($user_info['pics'][$i]['file'], 172);
    	$size_two = ChangePicSize($user_info['pics'][$i]['file'], 320);
?>
			                    <li name="<?php echo $size_two; ?>" data-toggle="modal" data-target="#gallery_modal">
			                        <a href="#">
			                            <img src="<?php echo $size_one; ?>" width="115" height="115" class="thumbnail" alt="<?php echo $user_info['name']; ?>">
			                        </a>
			                    </li>
<?php
    }
?>
		                	</ul>

		                	<div class="clearfix"></div>
							
							<!-- Link to report the user -->
<?php
	if($report) {
?>
							<a href="#" data-toggle="modal" data-target="#report_modal"><i class="fa fa-ban fa-lg"></i> Report <?php echo $user_info['name']; ?></a>
<?php
	}
?>
		           		</div>

						<!-- Resize stat buttons -->
						<div id="resize_stripe">
							<div class="row">
<?php
	for($i=0;$i<count($stats);$i++) {
		$id = ($stats[$i]['name'] == $tab_active ? 'active' : '');
?>
				                <div class="col-lg-2 timer_box" id="<?php echo $id; ?>" name="<?php echo $stats[$i]['name']; ?>">
				                	<div class="inside">
				                    	<h3>
				                    		<?php echo strtoupper($stats[$i]['name']); ?>
				                    	</h3>

				                    	<p><?php echo $stats[$i]['count']; ?></p>
									</div>

				                    <div class="border_active"></div>
				                </div>
<?php
	}
?>
							</div>
						</div>

			            <div class="col-lg-9 text-center">
			            	<div id="con_wrapper" class="panel panel-default">
			            		<div class="panel-heading">
									<ul>
<?php
	// Loop thru the connections tabs
	for($i=0;$i<count($tabs);$i++) {
		$tab_id = ($tabs[$i] == $sub_tab ? 'id="active"' : '');
?>
										<li <?php echo $tab_id; ?> name="<?php echo $tabs[$i]; ?>"><?php echo str_replace('_', ' ', $tabs[$i]); ?></li>
<?php
	}
?>
									</ul>
				            	</div>

				            	<div class="panel-body">
									<!-- Search bar for filtering connections -->
									<div id="search_con_container">
						                <div class="input-group">
							                <span class="input-group-addon"><i class="fa fa-<?php echo $con_icon; ?> fa-2x" id="fa_type"></i></span>
						                   	<input type="text" class="form-control" placeholder="Search <?php echo $tab_active; ?>" id="search_connections" autocomplete="off">
						            	</div>
						            </div>
					            
									<!-- Connections box where users are loaded -->
						            <div id="connections_box">
						                <div id="con_load_box">
						                    <div class="ajax-loader">
						                        <i class="fa fa-circle-o-notch fa-2x fa-spin"></i>
						                    </div>
						                </div>

						                <div class="hidden" id="type_name">matches</div>
						            </div>

						        	<div class="clearfix"></div>
						        </div>
					        </div>

					        <div class="clearfix"></div>
					    </div>

					    <div class="clearfix"></div>
		        	</div>
		        </div>

		        <div class="clearfix"></div>
            </div>
<?php
    // If the user is logged in and they aren't viewing their own profile, then display a link to report them along with a modal
    if($report) {
?>
			<!-- Report modal -->
            <div id="report_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                    	<form id="report_form" method="POST">
	                    	<div class="modal-header">
	                    		<h3 class="modal-title">
	                    			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
									Report <?php echo $user_info['name']; ?>
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

	                        <div class="modal-footer"></div>
	                    </form>
                    </div>
                </div>
            </div>
<?php
    }

    if($like['perm'] == 'can_like') {
?>
			<!-- Modal -->
			<div id="match_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog">
			    	<div class="modal-content">
			    		<div class="modal-header">
    						<h3 class="modal-title">
    							It's a match
								<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
    						</h3>
						</div>

			      		<div class="modal-body">
			        		<div class="col-lg-6 text-right">
			        			<div>
									<img src="<?php echo $user_info['profile_pic']; ?>" alt="<?php echo $user_info['name']; ?>">
								</div>
			        		</div>

			        		<div class="col-lg-6">
								<div>
									<img src="<?php echo $my_info['pic']; ?>" alt="<?php echo $my_info['name']; ?>" id="match_pic">
								</div>
			        		</div>

			        		<div class="col-lg-12 text-center">
			        			<p>
									<a href="<?php echo $base_url.$user_info['link']; ?>"><?php echo $user_info['name']; ?></a> <span>&</span> <a href="<?php echo $my_info['link']; ?>" id="match_name"><?php echo $my_info['name']; ?></a>
								</p>
			        		</div>

			        		<div class="clearfix"></div>
			      		</div>

			      		<div class="modal-footer">
			      			<button class="btn btn-success" type="button" id="msg_match">Send <?php echo $user_info['name']; ?> a message</button>
			      			<button class="btn btn-primary" type="button" data-dismiss="modal">Keep Playing</button>
			      		</div>
			    	</div>
			  	</div>
			</div>
<?php
    }
?>
        </div>

		<!-- Gallery modal -->
        <div id="gallery_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                	<div class="modal-header">
                        <h3 class="modal-title text-center">
                        	<?php echo $user_info['name']; ?>
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        </h3>
                    </div>

                    <div class="modal-body text-center">
                		<img src="" alt="<?php echo $user_info['name']; ?>" id="gallery_img" class="thumbnail">
                	</div>

                	<div class="modal-footer">
                		<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                	</div>
                </div>
            </div>
        </div>

		<!-- Write all of the JS variables -->
        <div class="hidden" id="user_tinder_id"><?php echo $user_info['tinder_id']; ?></div>
        <div class="hidden" id="can_edit"><?php echo $edit; ?></div>
        <div class="hidden" id="like"><?php echo $like['perm']; ?></div>
        <div class="hidden" id="match_id"><?php echo $like['match_id']; ?></div>
        <div class="hidden" id="lon"><?php echo $lon; ?></div>
        <div class="hidden" id="lat"><?php echo $lat; ?></div>
        <div class="hidden" id="radius"><?php echo $distance; ?></div>
        <div class="hidden" id="twitter"><?php echo $twitter['access']; ?></div>
        <div class="hidden" id="handle"><?php echo $twitter['handle']; ?></div>
        <div class="hidden" id="twitter_id"><?php echo $twitter['id']; ?></div>
        <div class="hidden" id="first_name"><?php echo $user_info['name']; ?></div>
        <div class="hidden" id="gender"><?php echo $user_info['gender']; ?></div>
        <div class="hidden" id="active_tab"><?php echo $tab_active; ?></div>
    </div>