<?php $title = __('nav.about'); ?>
<section class="site-wrap narrow">
  <h1 class="section-title section-title--lg"><?= e(__('about.title')) ?></h1>
  <p class="hero__lead"><?= e(__('about.p1')) ?></p>
  <div class="row g-3 mt-2">
    <?php foreach (['Student' => 'cap', 'Company' => 'building', 'Supervisor' => 'users', 'Admin' => 'shield'] as $r => $ic): ?>
      <div class="col-sm-6"><div class="co-card h-100"><div class="co-card__head"><span class="co-tile co-tile--lavender"><?= icon($ic) ?></span><h2 class="co-card__title"><?= e(__('role.' . $r)) ?></h2></div>
        <p class="co-caption steps__text"><?= e(__('about.' . $r)) ?></p></div></div>
    <?php endforeach; ?>
  </div>
  <h2 class="section-title mt-5"><?= e(__('about.privacy_t')) ?></h2>
  <p id="privacy" class="steps__text"><?= e(__('about.privacy')) ?></p>
</section>
