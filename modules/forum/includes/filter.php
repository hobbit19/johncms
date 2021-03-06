<?php

/**
 * This file is part of JohnCMS Content Management System.
 *
 * @copyright JohnCMS Community
 * @license   https://opensource.org/licenses/GPL-3.0 GPL-3.0
 * @link      https://johncms.com JohnCMS Project
 */

declare(strict_types=1);

defined('_IN_JOHNCMS') || die('Error: restricted access');

/**
 * @var Johncms\System\Utility\Tools $tools
 */

if (! $id) {
    echo $view->render(
        'system::pages/result',
        [
            'title'         => _t('Filter by author'),
            'page_title'    => _t('Filter by author'),
            'type'          => 'alert-danger',
            'message'       => _t('Wrong data'),
            'back_url'      => '/forum/',
            'back_url_name' => _t('Back'),
        ]
    );
    exit;
}

switch ($do) {
    case 'unset':
        // Удаляем фильтр
        unset($_SESSION['fsort_id'], $_SESSION['fsort_users']);

        header("Location: ?type=topic&id=${id}");
        break;

    case 'set':
        // Устанавливаем фильтр по авторам
        $users = $_POST['users'] ?? '';

        if (empty($_POST['users'])) {
            echo $view->render(
                'system::pages/result',
                [
                    'title'         => _t('Filter by author'),
                    'page_title'    => _t('Filter by author'),
                    'type'          => 'alert-danger',
                    'message'       => _t('You have not selected any author'),
                    'back_url'      => '?type=topic&act=filter&amp;id=' . $id . '&amp;start=' . $start,
                    'back_url_name' => _t('Back'),
                ]
            );
            exit;
        }

        $array = [];

        foreach ($users as $val) {
            $array[] = (int) $val;
        }

        $_SESSION['fsort_id'] = $id;
        $_SESSION['fsort_users'] = serialize($array);
        header("Location: ?type=topic&id=${id}");
        break;

    default:
        /** @var PDO $db */
        $db = di(PDO::class);
        // Показываем список авторов темы, с возможностью выбора
        $req = $db->query("SELECT *, COUNT(`user_id`) AS `count` FROM `forum_messages` WHERE `topic_id` = '${id}' GROUP BY `user_id` ORDER BY `user_name`");
        $total = $req->rowCount();
        if ($total) {
            $list = [];
            while ($res = $req->fetch()) {
                $list[] = $res;
            }
        } else {
            echo $view->render(
                'system::pages/result',
                [
                    'title'         => _t('Filter by author'),
                    'page_title'    => _t('Filter by author'),
                    'type'          => 'alert-danger',
                    'message'       => _t('Wrong data'),
                    'back_url'      => '?type=topic&id=' . $id . '&amp;start=' . $start,
                    'back_url_name' => _t('Back'),
                ]
            );
            exit;
        }
}

echo $view->render(
    'forum::filter_by_author',
    [
        'title'      => _t('Filter by author'),
        'page_title' => _t('Filter by author'),
        'id'         => $id,
        'start'      => $start,
        'back_url'   => '?type=topic&id=' . $id . '&amp;start=' . $start,
        'total'      => $total,
        'list'       => $list ?? [],
        'topic'      => $topic ?? [],
        'saved'      => $saved ?? false,
    ]
);
