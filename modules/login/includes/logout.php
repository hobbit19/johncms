<?php

declare(strict_types=1);

/*
 * This file is part of JohnCMS Content Management System.
 *
 * @copyright JohnCMS Community
 * @license   https://opensource.org/licenses/GPL-3.0 GPL-3.0
 * @link      https://johncms.com JohnCMS Project
 */

/** @var Johncms\Api\ConfigInterface $config */
$config = App::getContainer()->get(Johncms\Api\ConfigInterface::class);

$referer = isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : $config->homeurl;

if (isset($_POST['submit'])) {
    setcookie('cuid', '', time() - 3600, '/');
    setcookie('cups', '', time() - 3600, '/');
    session_destroy();
    header('Location: ' . $config->homeurl);
} else {
    require 'system/head.php';
    echo '<div class="rmenu">' .
        '<p>' . _t('Are you sure you want to leave the site?', 'system') . '</p>' .
        '<form method="post"><p><input type="submit" name="submit" value="' . _t('Logout', 'system') . '" /></p></form>' .
        '<p><a href="' . $referer . '">' . _t('Cancel', 'system') . '</a></p>' .
        '</div>';
    require 'system/end.php';
}
