<?php
// Exit if File is called Directly
if (!defined('ABSPATH')) {
exit;
}
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
    <link rel="stylesheet" href="{{ page.styleUrl }}">
    <script>
        const nonce = '{{ page.nonce }}';
        const ajax_url = '{{ page.ajaxUrl }}';
    </script>
</head>
<body>
<section class="container">
    <br><br><br>
    <h2>{{ page.heading }}</h2>
    <p>{{ page.viewSearchText }}<a href="#" onclick="return false;">https://jsonplaceholder.typicode.com/users</a></p>
    {% if page.canManageOptions %}
        {{ page.settingsText | raw }}
    {% endif %}

    {% if page.isSearchFields %}
    <!-- Search form Section-->
    <div class="row" style="padding: 2px;">
        <div class="md-form active-purple active-purple-2 mb-3 mt-0 col-8" style="margin: 0px !important; padding:0px;border-style: none;">
            <input class="form-control" type="text" placeholder="Search By" id="search-input" aria-label="Search">
        </div>

        <div class="col-2" style="margin:0px;padding:0px;border-style:none;">
            <input type="text"
                   value="{{ page.searchByText }}"
                   class="form-control"
                   disabled/>
        </div>

        <select class="browser-default custom-select form-control col-2" id="select_options">
        {% for field in page.searchFields %}
           <option value="{{ field }}">{{ page.ucField(field) }}</option>
        {% endfor %}
        </select>
    </div>

    {% else %}
        <br/><br/>
    {% endif %}

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
                    <p><span class="label">Name:</span> <span class="value" id="name"></span> </p>
                    <p><span class="label">Username:</span> <span class="value" id="username"></span> </p>
                    <p><span class="label">Email    :</span> <span class="value" id="email"></span> </p>
                    <p><span class="label">Address:</span></p>
                    <p><span class="label">&emsp;Street:</span>  <span class="value" id="street"></span></p>
                    <p><span class="label">&emsp;Suite:</span>  <span class="value" id="suite"></span></p>
                    <p><span class="label">&emsp;City:</span>  <span class="value" id="city"></span></p>
                    <p><span class="label">&emsp;Zipcode:</span>  <span class="value" id="zipcode"></span></p>
                    <p>&emsp;<span class="label">Geo:</span></p>
                    <p><span class="label">&emsp;&emsp;Lat:</span>  <span class="value" id="lat"></span></p>
                    <p><span class="label">&emsp;&emsp;Lng:</span>  <span class="value" id="lng"></span></p>

                    <p><span class="label">Phone:</span> <span class="value" id="phone"></span> </p>
                    <p><span class="label">Website:</span> <span class="value" id="website"></span> </p>
                    <p><span class="label">Company:</span></p>
                    <p><span class="label">&emsp;Name:</span>  <span class="value" id="companyName"></span></p>
                    <p><span class="label">&emsp;Catch Phrase:</span>  <span class="value" id="companyCatchPhrase"></span></p>
                    <p><span class="label">&emsp;Bs:</span>  <span class="value" id="companyBs"></span></p>
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

<script src="{{ page.scriptUrl }}"></script>
</body>
</html>