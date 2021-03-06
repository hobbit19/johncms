<?php

/**
 * This file is part of JohnCMS Content Management System.
 *
 * @copyright JohnCMS Community
 * @license   https://opensource.org/licenses/GPL-3.0 GPL-3.0
 * @link      https://johncms.com JohnCMS Project
 */

declare(strict_types=1);

use Johncms\System\Http\Request;
use Johncms\System\Users\User;
use Johncms\System\Utility\Tools;
use Johncms\System\View\Extension\Assets;
use Johncms\System\View\Render;
use Johncms\Utility\NavChain;
use Zend\I18n\Translator\Translator;

defined('_IN_JOHNCMS') || die('Error: restricted access');

/**
 * @var Assets $assets
 * @var PDO $db
 * @var Tools $tools
 * @var User $user
 * @var Render $view
 */

$assets = di(Assets::class);
$db = di(PDO::class);
$user = di(User::class);
$tools = di(Tools::class);
$view = di(Render::class);

/** @var NavChain $nav_chain */
$nav_chain = di(NavChain::class);

/** @var Request $request */
$request = di(Request::class);

// Регистрируем папку с языками модуля
di(Translator::class)->addTranslationFilePattern('gettext', __DIR__ . '/locale', '/%s/default.mo');

$loader = new Aura\Autoload\Loader();
$loader->register();
$loader->addPrefix('Albums', __DIR__ . '/lib');

// Регистрируем Namespace для шаблонов модуля
$view->addFolder('album', __DIR__ . '/templates/');

$title = _t('Albums');

// Добавляем раздел в навигационную цепочку
$nav_chain->add($title, '/album/');

$id = $request->getQuery('id', 0, FILTER_SANITIZE_NUMBER_INT);
$act = $request->getQuery('act', 'index');
$mod = $request->getQuery('mod', '', FILTER_SANITIZE_STRING);
$al = $request->getQuery('al', null, FILTER_SANITIZE_NUMBER_INT);
$img = $request->getQuery('img', null, FILTER_SANITIZE_NUMBER_INT);


$max_album = 20;
$max_photo = 400;

// Закрываем от неавторизованных юзеров
if (! $user->isValid()) {
    echo $view->render(
        'system::pages/result',
        [
            'title'   => $title,
            'type'    => 'alert-danger',
            'message' => _t('For registered users only'),
        ]
    );
    exit;
}

// Получаем данные пользователя
/** @var int $user_id */
$user_id = $request->getQuery('user', $user->id, FILTER_SANITIZE_NUMBER_INT);
$req = $db->query('SELECT * FROM `users` WHERE `id` = ' . $user_id);
if (! $foundUser = $req->fetch()) {
    echo $view->render(
        'system::pages/result',
        [
            'title'   => $title,
            'type'    => 'alert-danger',
            'message' => _t('User does not exists'),
        ]
    );
    exit;
}

$actions = [
    'comments',
    'delete',
    'edit',
    'image_delete',
    'image_download',
    'image_edit',
    'image_move',
    'image_upload',
    'list',
    'new_comm',
    'show',
    'sort',
    'top',
    'users',
    'vote',
    'index',
];

if (($key = array_search($act, $actions, true)) !== false) {
    require __DIR__ . '/includes/' . $actions[$key] . '.php';
} else {
    pageNotFound();
}
