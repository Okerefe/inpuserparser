<?php
$scriptUrl = plugins_url('../public/js/script.js', __FILE__);
$styleUrl = plugins_url('../public/css/style.css', __FILE__);
$nonce = wp_create_nonce('inpuserparser_hook');
$ajaxUrl = admin_url('admin-ajax.php');

/*
 * Html Mark Up for InpUserParser Public Page
 * */
?>
<!Doctype html>
<html lang="en">
<head>
    <title>InpUserParser</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet"
          href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T"
          crossorigin="anonymous">
    <link rel="stylesheet" href="<?php echo esc_url($styleUrl); ?>">
    <script>
        const nonce = '<?php echo esc_attr($nonce);?>';
        const ajax_url = '<?php echo esc_url($ajaxUrl);?>';
    </script>
</head>
<body>
<section class="container">
    <br><br><br>

    <h2><?php echo esc_html__('InpUserParser', 'inpuserparser'); ?></h2>
    <p><?php echo esc_html__('View and Search for User Details from: ', 'inpuserparser'); ?>
        <a href="#" onclick="return false;">https://jsonplaceholder.typicode.com/users</a></p>

        <?php
        if (current_user_can('manage_options')) {
            echo '<p>'.esc_html__('Visit InpUserParser', 'inpuserparser').' '
                .InpUserParser\Settings::getSettingsLink().'</p>';
        }

        $selectMarkUp = "<br><br>";
        if (!empty(InpUserParser\Settings::visibleSearchFields())) {
            ?>
        <!-- Search form Section-->
        <div class="row" style="padding: 2px;">
            <div class="md-form active-purple active-purple-2 mb-3 mt-0 col-8" style="margin: 0px !important; padding:0px;border-style: none;">
                <input class="form-control" type="text" placeholder="Search By" id="search-input" aria-label="Search">
            </div>

            <div class="col-2" style="margin:0px;padding:0px;border-style:none;">
                <input type="text" value="<?php echo esc_html__('Search By:', 'inpuserparser')?>"
                       disabled class="form-control"/>
            </div>

            <select class="browser-default custom-select form-control col-2" id="select_options">
                <?php
                $selectMarkUp = "";
                foreach (InpUserParser\Settings::visibleSearchFields() as $field) {
                    $selectMarkUp .= '<option value="'.$field.'">'.InpUserParser\Settings::ucFields($field).'</option>';
                }
                echo $selectMarkUp;
                $selectMarkUp = "";
                ?>
            </select>

        </div>
            <?php
        }

            echo $selectMarkUp;
        ?>

    <br><br>

    <div style="overflow-x: auto;">
        <p id="error" class="text-danger" style="display: none;"></p>
        <p id="search-info" class="text-warning" style="display: none;"></p>

        <div class="spinner-cover" id="spinner-cover" style="display:none;">
            <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
        </div>
        <div id="table-cover" style="display: none;"></div>
    </div>
</section>

<a href="#details" data-toggle="modal" style="display:none;" id="details-activate"></a>
<div id="details" class="modal hide fade in" style="color:black;"data-keyboard="false" data-backdrop="static"><!--Edit Description popover-->
    <div class="modal-dialog">
        <div class="modal-content" id="location-comment-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">InpUserDetails:</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="spinner-cover" id="modal-spinner-cover" style="display:none;">
                    <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
                </div>
                <p id="modal-error" class="text-danger" style="display: none;"></p>
                <div id="user-contents" style="display: none;">
                    <p><span class="label"> Id:</span> <span class="value" id="id"> 4 </span> </p>
                    <p><span class="label">Name:</span> <span class="value" id="name">Donald J Trump</span> </p>
                    <p><span class="label">Username:</span> <span class="value" id="username">Donald J Trump</span> </p>
                    <p><span class="label">Email    :</span> <span class="value" id="email">Donald J Trump</span> </p>
                <p><span class="label">Address:</span></p>
                    <p><span class="label">&emsp;Street:</span>  <span class="value" id="street">Kulas Light</span></p>
                    <p><span class="label">&emsp;Suite:</span>  <span class="value" id="suite">Kulas Light</span></p>
                    <p><span class="label">&emsp;City:</span>  <span class="value" id="city">Kulas Light</span></p>
                    <p><span class="label">&emsp;Zipcode:</span>  <span class="value" id="zipcode">Kulas Light</span></p>
                    <p>&emsp;<span class="label">Geo:</span></p>
                    <p><span class="label">&emsp;&emsp;Lat:</span>  <span class="value" id="lat">Kulas Light</span></p>
                    <p><span class="label">&emsp;&emsp;Lng:</span>  <span class="value" id="lng">Kulas Light</span></p>

                    <p><span class="label">Phone:</span> <span class="value" id="phone">58383843</span> </p>
                    <p><span class="label">Website:</span> <span class="value" id="website">www.ffdwewed.org</span> </p>
                    <p><span class="label">Company:</span></p>
                    <p><span class="label">&emsp;Name:</span>  <span class="value" id="companyName">Kulas Light</span></p>
                    <p><span class="label">&emsp;Catch Phrase:</span>  <span class="value" id="companyCatchPhrase">Kulas Light</span></p>
                    <p><span class="label">&emsp;Bs:</span>  <span class="value" id="companyBs">Kulas Light</span></p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>

        </div><!-- modal-content-->
    </div><!-- modal-dialog -->
</div><!-- modal fade for pictures form-->


<script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
        crossorigin="anonymous">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        crossorigin="anonymous">
</script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        crossorigin="anonymous">
</script>

<script src="<?php echo esc_url($scriptUrl); ?>"></script>
</body>
</html>