<?php
    $base_url = $this->config->base_url();
?>
	<div id="header-section">
        <div id="signin">
            <h1 class="page-header" itemscope itemtype="http://schema.org/WebPage">
                <i class="fa fa-facebook-square"></i> 

                <span itemprop="headline"><?php echo $header; ?></span>
            </h1>

            <form method="post" action="<?php echo $base_url; ?>signin/login" id="signin_form">
                <input type="text" class="form-control" placeholder="Username, Email or Phone Number" name="username"><br>
                <input type="password" class="form-control" placeholder="Password" name="password"><br>
                <button class="btn btn-primary pull-right" type="submit" name="submit" value="submit">Sign in</button>
                <div class="clearfix"></div>
            </form>

            <hr>

            <span class="pull-right">By signing in, you accept the <a href="#" data-toggle="modal" data-target="#terms_modal">Terms of Service</a></span>

            <div class="clearfix"></div>
        </div>

        <!-- The terms of service modal -->
        <div class="modal fade" id="terms_modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                            Terms of Service
                        </h3>
                    </div>

                    <div class="modal-body">
                        <div class="col-lg-12 text-center">
                            <p></p>
                        </div>

                        <div class="clearfix"></div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-success" type="button" id="accept_terms">Accept</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- The syncing modal -->
        <div class="modal fade" id="sync_modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">
                            Syncing your account...
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        </h3>
                    </div>

                    <div class="modal-body text-center">
                        <div class="ajax-loader">
                            <i class="fa fa-refresh fa-spin fa-4x"></i>
                        </div>
                    </div>

                    <div class="modal-footer">
                        
                    </div>
                </div>
            </div>
        </div>

        <meta content="Dating" itemprop="genre">
        <meta content="url" itemprop="http://twinder.io/">
        <meta content="1" itemprop="version">
        <meta content="Tinder, Twinder, Tinder for Web, Twinder.io, Tinder Online, Tinder Client, Tinder Web Client, Tinder API" itemprop="keywords">
        <meta content="2015" itemprop="copyrightYear">
        <meta content="2015-04-01" itemprop="dateCreated">
    </div>