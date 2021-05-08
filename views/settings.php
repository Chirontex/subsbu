<?php
/**
 * @package Subsbu
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 * @since 0.1.3
 */
if (!defined('ABSPATH')) die;

?>
<div class="container">
    <h1 class="h3 text-center my-5">Subsbu: настройки</h1>
    <div class="main-column">
        <h4 class="text-center mb-3">Настройка уведомления по e-mail</h4>
        <ul class="list-group mb-3">
            <li class="list-group-item list-group-item-light">
                !%site_url%! — адрес сайта
            </li>
            <li class="list-group-item list-group-item-light">
                !%site_name%! — название сайта
            </li>
            <li class="list-group-item list-group-item-light">
                !%mail_time%! — время до начала мероприятия (задаётся в настройках ниже)
            </li>
            <li class="list-group-item list-group-item-light">
                !%event_name%! — название мероприятия
            </li>
            <li class="list-group-item list-group-item-light">
                !%event_url%! — ссылка на страницу мероприятия
            </li>
        </ul>
        <form action="" method="post">
            <?php wp_nonce_field('subsbuSettingsNonce-wpnp', 'subsbuSettingsNonce') ?>
            <div class="mb-3">
                <label for="subsbuMailSenderName" class="form-label">Имя отправителя:</label>
                <input type="text" name="subsbuMailSenderName" id="subsbuMailSenderName" class="form-control form-control-sm" placeholder="укажите тему письма" value="<?= htmlspecialchars(apply_filters('subsbu-mail-sender-name', '')) ?>" required="true">
            </div>
            <div class="mb-3">
                <label for="subsbuMailSenderEmail" class="form-label">Адрес отправителя:</label>
                <input type="email" name="subsbuMailSenderEmail" id="subsbuMailSenderEmail" class="form-control form-control-sm" placeholder="укажите тему письма" value="<?= htmlspecialchars(apply_filters('subsbu-mail-sender-email', '')) ?>" required="true">
            </div>
            <div class="mb-3">
                <label for="subsbuMailTime" class="form-label">За сколько минут до начала должно приходить уведомление:</label>
                <input type="number" name="subsbuMailTime" id="subsbuMailTime" class="form-control form-control-sm" placeholder="значение в минутах" value="<?= htmlspecialchars(apply_filters('subsbu-mail-time', '')) ?>" required="true">
            </div>
            <div class="mb-3">
                <label for="subsbuMailSubject" class="form-label">Тема письма:</label>
                <input type="text" name="subsbuMailSubject" id="subsbuMailSubject" class="form-control form-control-sm" placeholder="укажите тему письма" value="<?= htmlspecialchars(apply_filters('subsbu-mail-subject', '')) ?>" required="true">
            </div>
            <div class="mb-3">
                <label for="subsbuMailText" class="form-label">Текст письма:</label>
                <textarea name="subsbuMailText" id="subsbuMailText" class="form-control form-control-sm" cols="30" rows="10" placeholder="текст письма" required="true"><?= htmlspecialchars(apply_filters('subsbu-mail-text', '')) ?></textarea>
            </div>
            <div class="mb-3 text-center">
                <button type="submit" class="button button-primary">Сохранить</button>
            </div>
        </form>
    </div>
</div>