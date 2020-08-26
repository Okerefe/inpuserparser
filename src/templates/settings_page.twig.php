<?php
// Exit if File is called Directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1>{{ settingsPage.adminPageTitle }}</h1>
    <a href="{{ settingsPage.previewUrl }}">{{ settingsPage.previewUrlText }}</a>
    <form action="options.php" method="post">

                <!-- output security fields -->
                {{ settingsPage.settingsField() }}

                <!--output setting sections -->
                {{ settingsPage.settingsSection() }}

                <!--output submit button -->
                {{ settingsPage.submitButton() }}
    </form>
</div>