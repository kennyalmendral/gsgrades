<?php

if (!is_user_logged_in()) {
    wp_redirect(LOGIN_PAGE_URL);
    exit;
}

get_header();

?>

<div id="page-title" class="bg-white shadow-sm py-3">
    <div class="container">
        <div class="d-flex flex-row align-items-center justify-content-between mb-3">
            <h1 class="p-0 mb-0 fs-2">Students</h1>

            <div class="btn-group">
                <button class="btn btn-outline-secondary border-end-0"><i class="bi bi-person-plus"></i> Add new</button>
                <button class="btn btn-outline-secondary"><i class="bi bi-download"></i> Export to spreadsheet</button>
                <button class="btn btn-outline-secondary border-start-0"><i class="bi bi-download"></i> Export to PDF</button>
            </div>
        </div>
    </div>
</div>

<div class="container my-3">
    <!--<div class="bg-white shadow-sm px-4 py-3 rounded">
        <p>Consectetur sint autem atque cupiditate et tempora? Ab ipsam nulla facilis iusto consectetur Quibusdam dolor animi sapiente mollitia accusamus Obcaecati suscipit dolorem eligendi inventore sapiente mollitia, quidem Eos asperiores ipsa</p>
    </div>-->
</div>

<?php get_footer(); ?>
