<?php

declare(strict_types=1);

/*
 * This file is part of JohnCMS Content Management System.
 *
 * @copyright JohnCMS Community
 * @license   https://opensource.org/licenses/GPL-3.0 GPL-3.0
 * @link      https://johncms.com JohnCMS Project
 */

defined('_IN_JOHNCMS') || die('Error: restricted access');

use Library\Hashtags;
use Library\Rating;

$sort = isset($_GET['sort']) && $_GET['sort'] == 'rating' ? 'rating' : (isset($_GET['sort']) && $_GET['sort'] == 'comm' ? 'comm' : 'read');

$menu[] = $sort == 'read' ? '<strong>' . _t('Most readings') . '</strong>' : '<a href="?act=top&amp;sort=read">' . _t('Most readings') . '</a> ';
$menu[] = $sort == 'rating' ? '<strong>' . _t('By rating') . '</strong>' : '<a href="?act=top&amp;sort=rating">' . _t('By rating') . '</a> ';
$menu[] = $sort == 'comm' ? '<strong>' . _t('By comments') . '</strong>' : '<a href="?act=top&amp;sort=comm">' . _t('By comments') . '</a>';

echo '<div class="phdr"><strong><a href="?">' . _t('Library') . '</a></strong> | ' . _t('Rating articles') . '</div>' .
    '<div class="topmenu">' . _t('Sort') . ': ' . implode(' | ', $menu) . '</div>';

if ($sort == 'read' || $sort == 'comm') {
    $total = $db->query('SELECT COUNT(*) FROM `library_texts` WHERE ' . ($sort == 'comm' ? '`comm_count`' : '`count_views`') . ' > 0 ORDER BY ' . ($sort == 'comm' ? '`comm_count`' : '`count_views`') . ' DESC LIMIT 20')->fetchColumn();
} else {
    $total = $db->query('SELECT COUNT(*) AS `cnt`, AVG(`point`) AS `avg` FROM `cms_library_rating` GROUP BY `st_id` ORDER BY `avg` DESC, `cnt` DESC LIMIT 20')->fetchColumn(0);
}

$page = $page >= ceil($total / $user->config->kmess) ? ceil($total / $user->config->kmess) : $page;
$start = $page == 1 ? 0 : ($page - 1) * $user->config->kmess;

if (! $total) {
    echo '<div class="menu"><p>' . _t('The list is empty') . '</p></div>';
} else {
    if ($sort == 'read' || $sort == 'comm') {
        $stmt = $db->query(
            'SELECT `id`, `name`, `time`, `uploader`, `uploader_id`, `count_views`, `cat_id`, `comments`, `comm_count`, `announce` FROM `library_texts`
            WHERE ' . ($sort == 'comm' ? '`comm_count`' : '`count_views`') . ' > 0
            ORDER BY ' . ($sort == 'comm' ? '`comm_count`' : '`count_views`') . ' DESC
            LIMIT ' . $start . ',' . $user->config->kmess
        );
    } else {
        $stmt = $db->query(
            'SELECT `library_texts`.*, COUNT(*) AS `cnt`, AVG(`point`) AS `avg` FROM `cms_library_rating`
            JOIN `library_texts` ON `cms_library_rating`.`st_id` = `library_texts`.`id`
            GROUP BY `cms_library_rating`.`st_id`
            ORDER BY `avg` DESC, `cnt` DESC
            LIMIT ' . $start . ',' . $user->config->kmess
        );
    }

    $i = 0;

    while ($row = $stmt->fetch()) {
        echo '<div class="list' . (++$i % 2 ? 2 : 1) . '">'
            . (file_exists(UPLOAD_PATH . 'library/images/small/' . $row['id'] . '.png')
                ? '<div class="avatar"><img src="../upload/library/images/small/' . $row['id'] . '.png" alt="screen" /></div>'
                : '')
            . '<div class="righttable"><h4><a href="?id=' . $row['id'] . '">' . $tools->checkout($row['name']) . '</a></h4>'
            . '<div><small>' . $tools->checkout($row['announce'], 0, 2) . '</small></div></div>';

        // Описание к статье
        $obj = new Hashtags($row['id']);
        $rate = new Rating($row['id']);
        $uploader = $row['uploader_id'] ? '<a href="' . di('config')['johncms']['homeurl'] . '/profile/?user=' . $row['uploader_id'] . '">' . $tools->checkout($row['uploader']) . '</a>' : $tools->checkout($row['uploader']);
        echo '<table class="desc">'
            // Раздел
            . '<tr>'
            . '<td class="caption">' . _t('Section') . ':</td>'
            . '<td><a href="?do=dir&amp;id=' . $row['cat_id'] . '">' . $tools->checkout($db->query('SELECT `name` FROM `library_cats` WHERE `id`=' . $row['cat_id'])->fetchColumn()) . '</a></td>'
            . '</tr>'
            // Тэги
            . ($obj->getAllStatTags() ? '<tr><td class="caption">' . _t('Tags') . ':</td><td>' . $obj->getAllStatTags(1) . '</td></tr>' : '')
            // Кто добавил?
            . '<tr>'
            . '<td class="caption">' . _t('Who added') . ':</td>'
            . '<td>' . $uploader . ' (' . $tools->displayDate($row['time']) . ')</td>'
            . '</tr>'
            // Рейтинг
            . '<tr>'
            . '<td class="caption">' . _t('Rating') . ':</td>'
            . '<td>' . $rate->viewRate(1) . '</td>'
            . '</tr>'
            // Прочтений
            . '<tr>'
            . '<td class="caption">' . _t('Number of readings') . ':</td>'
            . '<td>' . $row['count_views'] . '</td>'
            . '</tr>'
            // Комментарии
            . '<tr>';
        if ($row['comments']) {
            echo '<td class="caption"><a href="?act=comments&amp;id=' . $row['id'] . '">' . _t('Comments') . '</a>:</td><td>' . $row['comm_count'] . '</td>';
        } else {
            echo '<td class="caption">' . _t('Comments') . ':</td><td>' . _t('Comments are closed') . '</td>';
        }
        echo '</tr></table>';

        echo '</div>';
    }
}

echo '<div class="phdr"><a href="?">' . _t('Back') . '</a></div>';
