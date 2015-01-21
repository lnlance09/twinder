<?php
    $base_url = $this->config->base_url();
    $public_url = $base_url.'public/';
    $img_url = $public_url.'img/';
?>
		<div id="header-section">
            <div id="signin">
                <h1 class="page-header">
                    <?php echo $header; ?>
                </h1>
                    
                <div class="col-lg-12">
                
                </div>

                <!--
                <form method="GET" action="<?php echo $base_url; ?>matches" id="search_messages">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Filter" name="search_messages">
                    </div>
                </form>
                -->

                <div id="matches_load">
                    <div class="ajax-loader">
                        
                    </div>
                </div>

                <div class="hidden" id="match_type"><?php echo $type; ?></div>

                <div id="fb-root"></div>
                <script>
                    (function(d, s, id) {
                        var js, fjs = d.getElementsByTagName(s)[0];
                        if (d.getElementById(id)) return;
                        js = d.createElement(s); js.id = id;
                        js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=1551292908455322&version=v2.0";
                        fjs.parentNode.insertBefore(js, fjs);
                    } (document, 'script', 'facebook-jssdk'));
                </script>

                <div class="fb-comments" data-href="http://developers.facebook.com/docs/plugins/comments/" data-numposts="10" data-colorscheme="light"></div>
            </div>
        </div>