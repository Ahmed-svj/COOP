<?php $title = __('nav.settings'); $u = Auth::user(); $noErrorSummary = true; ?>
<?= View::partial('partials/page-head', ['heading' => __('nav.settings'), 'sub' => __('settings.sub')]) ?>
<div class="row g-3">
  <div class="col-lg-6"><form class="co-card app-form" method="post" action="<?= url('/settings') ?>">
    <?= csrf_field() ?>
    <div class="co-card__head"><span class="co-tile"><?= icon('settings') ?></span><h2 class="co-card__title"><?= e(__('settings.prefs')) ?></h2></div>
    <div class="co-list">
      <label class="co-row"><span class="co-row__text"><span class="co-row__title"><?= e(__('settings.email')) ?></span><span class="co-row__sub"><?= e(__('settings.email_d')) ?></span></span><input type="checkbox" role="switch" class="co-toggle" name="email_notifications" value="1"<?= $prefs['email_notifications'] ? ' checked' : '' ?>></label>
      <label class="co-row"><span class="co-row__text"><span class="co-row__title"><?= e(__('settings.dark')) ?></span><span class="co-row__sub"><?= e(__('settings.dark_d')) ?></span></span><input type="checkbox" role="switch" class="co-toggle" name="dark_mode" value="1"<?= $prefs['dark_mode'] ? ' checked' : '' ?>></label>
    </div>
    <div class="co-field<?= invalid('phone') ?>"><label for="phone"><?= e(__('auth.phone')) ?></label><input class="co-input" id="phone" name="phone" dir="ltr" inputmode="numeric" value="<?= e($u['Phone']) ?>" data-rule="phone"><?= error_for('phone') ?></div>
    <div><button class="co-btn co-btn--primary" type="submit"><?= e(__('common.save')) ?></button></div>
  </form></div>
  <div class="col-lg-6 co-stack">
    <form class="co-card app-form" method="post" action="<?= url('/settings/email') ?>" data-validate novalidate>
      <?= csrf_field() ?>
      <div class="co-card__head"><span class="co-tile co-tile--mint"><?= icon('mail') ?></span><h2 class="co-card__title"><?= e(__('settings.change_email')) ?></h2></div>
      <p class="co-caption m-0"><?= e(__('settings.current_email')) ?>: <bdi dir="ltr"><?= e($u['Email']) ?></bdi></p>
      <div class="co-field<?= invalid('new_email') ?>"><label for="new_email"><?= e(__('settings.new_email')) ?></label><input class="co-input" id="new_email" name="new_email" type="email" dir="ltr" required autocomplete="email" value="<?= e(old('new_email')) ?>"<?= $u['Role'] === 'Student' ? ' data-domain="' . e(implode(',', student_domains())) . '"' : '' ?>><?= error_for('new_email') ?: ($u['Role'] === 'Student' ? '<span class="co-field__help">' . e(__('auth.inst_email_help', ['domain' => implode(', @', student_domains())])) . '</span>' : '') ?></div>
      <div class="co-field<?= invalid('email_password') ?>"><label for="email_password"><?= e(__('settings.current_password')) ?></label><input class="co-input" id="email_password" name="email_password" type="password" dir="ltr" required autocomplete="current-password"><?= error_for('email_password') ?: '<span class="co-field__help">' . e(__('settings.email_password_help')) . '</span>' ?></div>
      <div><button class="co-btn" type="submit"><?= e(__('settings.change_email')) ?></button></div>
    </form>
    <form class="co-card app-form" method="post" action="<?= url('/settings/password') ?>" data-validate novalidate>
      <?= csrf_field() ?>
      <div class="co-card__head"><span class="co-tile co-tile--lavender"><?= icon('lock') ?></span><h2 class="co-card__title"><?= e(__('settings.change_password')) ?></h2></div>
      <div class="co-field<?= invalid('current_password') ?>"><label for="cp"><?= e(__('settings.current_password')) ?></label><input class="co-input" id="cp" name="current_password" type="password" dir="ltr" required autocomplete="current-password"><?= error_for('current_password') ?></div>
      <div class="co-field<?= invalid('password') ?>"><label for="password"><?= e(__('reset.new')) ?></label><input class="co-input" id="password" name="password" type="password" dir="ltr" required data-rule="password" autocomplete="new-password"><?= error_for('password') ?></div>
      <div class="co-field"><label for="password_confirmation"><?= e(__('auth.password_confirm')) ?></label><input class="co-input" id="password_confirmation" name="password_confirmation" type="password" dir="ltr" required data-match="password" autocomplete="new-password"></div>
      <div><button class="co-btn" type="submit"><?= e(__('settings.change_password')) ?></button></div>
    </form>
    <form class="co-card app-form" method="post" action="<?= url('/settings/avatar') ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="co-card__head"><span class="co-tile co-tile--mint"><?= icon('user') ?></span><h2 class="co-card__title"><?= e(__('settings.avatar')) ?></h2></div>
      <div class="d-flex gap-3 align-items-center"><button type="button" class="app-avatar-btn app-avatar-btn--lg" data-avatar-pick aria-label="<?= e(__('settings.change_photo')) ?>"><?= avatar_html($u, 'app-avatar-lg') ?><span class="app-avatar-btn__cam" aria-hidden="true"><?= icon('camera', 'co-ico--sm') ?></span></button>
        <div class="co-field flex-grow-1"><label for="avatar" class="visually-hidden"><?= e(__('settings.avatar')) ?></label><input class="co-input app-file" id="avatar" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" required data-max="2097152"><span class="co-field__help"><?= e(__('settings.avatar_help')) ?></span></div></div>
      <div><button class="co-btn" type="submit"><?= e(__('settings.upload')) ?></button></div>
    </form>
  </div>
</div>
