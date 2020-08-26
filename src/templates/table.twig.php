<?php
// Exit if File is called Directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<table class="table table-hover">
    <thead>
        <tr>
            {% for column in columns %}
            <th scope="col">
                {{ helper.ucfields(column) }}
            </th>
            {% endfor %}
        </tr>
    </thead>
    <tbody>
            {% for user in users %}
            <tr>

                {% for column in columns %}
                <td><a href="" onclick="showDetail({{ user.id }}); return false;">
                    {{ attribute(user, column) }}
                </td>
                {% endfor %}

            </tr>
            {% endfor %}

    </tbody>
</table>