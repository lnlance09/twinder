<?php
    $base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $img_url = $public_url.'img/';
?>
	<div id="header-section">
        <div class="container" id="profile_top">
        	<!-- Radiating circle where new users will be loaded -->
        	<div id="users_load" class="text-center">
        		<div id="user_circle">
					<img src="<?php echo $this->session->userdata('profile_pic'); ?>" width="184" height="184" id="radar">
				</div>
            </div>

			<!-- Like or Pass buttons -->
			<div class="col-lg-12 text-center" id="like_or_pass">
				<div class="col-lg-6">
        			<img class="svg pull-right" id="pass_user" src="<?php echo $img_url; ?>svg/close.svg" width="500" height="500" alt="pass" title="hey">
        		</div>

        		<div class="col-lg-6">
                	<img class="svg pull-left" id="like_user" src="<?php echo $img_url; ?>svg/heart.svg" width="500" height="500" alt="like"> 
                </div>

                <div class="clearfix"></div>
       	 	</div>

			<div class="hidden" id="user_at_num">0</div>

			<!-- Modal -->
			<div class="modal fade" id="match_modal">
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
									<img src="<?php echo $pic; ?>" width="172" height="172" alt="<?php echo $name; ?>">
								</div>
			        		</div>

			        		<div class="col-lg-6">
								<div>
									<img src="" width="172" height="172" alt="" id="match_pic">
								</div>
			        		</div>

			        		<div class="col-lg-12 text-center">
			        			<h3>
									<a href="<?php echo $base_url.$link; ?>"><?php echo $name; ?></a> <span>&</span> <a href="#" id="match_name"></a>
								</h3>
			        		</div>

			        		<div class="clearfix"></div>
			      		</div>

			      		<div class="modal-footer">
			      			<button class="btn btn-success" type="button" id="msg_match"></button>
			      			<button class="btn btn-primary" type="button" data-dismiss="modal">Keep Playing</button>
			      		</div>
			    	</div>
			  	</div>
			</div>
        </div>
	</div>
		