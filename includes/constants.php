<?php

define('GSG_DEV_MODE', true);
define('GSG_THEME_URL', get_template_directory_uri());
define('GSG_VENDORS_URL', GSG_THEME_URL . '/vendors');
define('GSG_IMAGES_URL', GSG_THEME_URL . '/images');

define('LOGIN_PAGE_ID', 13);
define('LOGIN_PAGE_URL', get_permalink(LOGIN_PAGE_ID));

define('REGISTER_PAGE_ID', 16);
define('REGISTER_PAGE_URL', get_permalink(REGISTER_PAGE_ID));

define('ACCOUNT_PAGE_ID', 17);
define('ACCOUNT_PAGE_URL', get_permalink(ACCOUNT_PAGE_ID));

define('GRADES_PAGE_ID', 20);
define('GRADES_PAGE_URL', get_permalink(GRADES_PAGE_ID));

define('STUDENTS_PAGE_ID', 22);
define('STUDENTS_PAGE_URL', get_permalink(STUDENTS_PAGE_ID));
