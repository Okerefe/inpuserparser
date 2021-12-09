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
    <link rel="stylesheet" href="{{ page.styleUrl }}">
</head>
<body>
<section class="container" id="container">
    <br><br><br>
    <h2>{{ page.heading }}</h2>
    <p>{{ page.viewSearchText }}<a href="#" onclick="return false;">https://jsonplaceholder.typicode.com/users</a></p>
    {% if page.canManageOptions %}
        {{ page.settingsText | raw }}
    {% endif %}

    {% if page.isSearchFields %}
    <!-- Search form Section-->
    <!-- Displays Search Form only when search fields is enabled-->
    <search
        search-by-text="{{ page.searchByText }}"
        :fields={{ page.searchFields|json_encode|raw }}
        v-on:searchstr="updateSearchStr($event)">

    </search>
    {% else %}
        <br/><br/>
    {% endif %}

    <br><br>

    <!-- Users Table-->
    <users-table
        :search="search"
        :key="searchKey"
        nonce='{{ page.nonce }}'
        hook='{{ page.hook }}'
        ajax-url='{{ page.ajaxUrl }}'>
    </users-table>

</section>

<script src="{{ page.scriptUrl }}"></script>
</body>
</html>