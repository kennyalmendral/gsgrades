<?php

if (!is_user_logged_in()) {
    wp_redirect(LOGIN_PAGE_URL);
    exit;
}

get_header();

?>

<div id="main-content" class="container my-3">
    <div class="d-flex flex-row justify-content-between">
        <h1 class="mb-3">Account</h1>
    </div>

    <div class="bg-white shadow-sm px-4 py-3 rounded">
        <p>Consectetur sint autem atque cupiditate et tempora? Ab ipsam nulla facilis iusto consectetur Quibusdam dolor animi sapiente mollitia accusamus Obcaecati suscipit dolorem eligendi inventore sapiente mollitia, quidem Eos asperiores ipsa</p>
    </div>
</div>

<?php get_footer(); ?>
