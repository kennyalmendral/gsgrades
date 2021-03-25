<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta http-equiv="content-type" content="text/html; charset=<?php bloginfo('charset'); ?>" />
	<meta name="description" content="<?php bloginfo('description'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="apple-touch-icon" href="<?php echo GSG_THEME_URL; ?>/apple-touch-icon.png">
	<link rel="icon" type="image/png" href="<?php echo GSG_THEME_URL; ?>/favicon.png">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <div id="loader">
        <!-- By Sam Herbert (@sherb), for everyone. More @ http://goo.gl/7AJzbL -->
        <svg width="38" height="38" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg" stroke="#212529">
            <g fill="none" fill-rule="evenodd">
                <g transform="translate(1 1)" stroke-width="2">
                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"/>

                    <path d="M36 18c0-9.94-8.06-18-18-18">
                        <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"/>
                    </path>
                </g>
            </g>
        </svg>
    </div>

    <div id="wrapper">
        <?php if (gsg_is_account_page() || gsg_is_records_page() || gsg_is_grades_page() || gsg_is_students_page()): ?>
            <header>
                <nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-gradient">
                    <div class="container">
                        <a class="navbar-brand" href="<?php echo home_url(); ?>"><img src="<?php echo GSG_IMAGES_URL; ?>/logo.png" width="32" height="32" alt="Gottes Segen Grades"></a>

                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <?php $current_page = gsg_get_current_page(); ?>

                            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                <?php if (gsg_is_teacher()): ?>
                                    <li class="nav-item"><a class="nav-link <?php echo $current_page == 'records' ? 'active' : '' ; ?>" href="<?php echo RECORDS_PAGE_URL; ?>">Records</a></li>
                                    <li class="nav-item"><a class="nav-link <?php echo $current_page == 'students' ? 'active' : '' ; ?>" href="<?php echo STUDENTS_PAGE_URL; ?>">Students</a></li>
                                <?php elseif (gsg_is_student()): ?>
                                    <li class="nav-item"><a class="nav-link <?php echo $current_page == 'grades' ? 'active' : '' ; ?>" href="<?php echo GRADES_PAGE_URL; ?>">Grades</a></li>
                                <?php endif; ?>
                            </ul>

                            <ul class="navbar-nav">
                                <li class="nav-item"><a class="nav-link <?php echo $current_page == 'account' ? 'active' : '' ; ?>" href="<?php echo ACCOUNT_PAGE_URL; ?>">Account</a></li>
                                <li class="nav-item"><a id="logout" class="nav-link" href="#">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </header>
        <?php endif; ?>
