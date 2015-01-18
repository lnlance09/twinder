        <div id="header-section">
            <div class="container" id="profile_top">
                <div id="single_users_load"></div>
<?php
    // If the user is logged in and they aren't viewing their own profile, then display a link to report them along with a modal
    if($session !== FALSE
    && $tinder_id != $this->session->userdata('tinder_id')) {
?>
	            <a href="#" id="report_user" data-toggle="modal" data-target="#report_modal"><i class="fa fa-send-o"></i> Report <?php echo $name; ?></a>

	            <div id="report_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	                <div class="modal-dialog">
	                    <div class="modal-content">
	                    	<form id="report_form" method="POST">
		                    	<div class="modal-header">
		                    		<h3>
		                    			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
										<i class="fa fa-bullhorn fa-md"></i> Report <?php echo $name; ?>
									</h3>
		                    	</div>

		                        <div class="modal-body">
		                        	<ul class="list-group">
			                            <li class="list-group-item">feels like spam</li>
			                            <li class="list-group-item">inappropriate or offensive</li>
			                            <li class="list-group-item" id="other_trigger">other...</li>
		                        	</ul>

									<div id="other_box">
		                        		<textarea class="form-control" placeholder="Why are you reporting this?" id="other_comment"></textarea>

		                        		<br><button class="btn btn-primary pull-right" type="submit" id="accept_terms">Submit</button>

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

            <div class="hidden" id="user_tinder_id"><?php echo $tinder_id; ?></div>
            <div class="hidden" id="can_edit"><?php echo $edit; ?></div>
        </div>
