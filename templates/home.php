<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="mortify-content space-y-6">
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <article class="bg-white shadow rounded-2xl p-5">
                <h1 class="text-xl font-semibold mb-3"><?php the_title(); ?></h1>
                <div class="prose max-w-none"><?php the_content(); ?></div>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <p><?php esc_html_e( 'No content found for this view.', 'mortify2026' ); ?></p>
    <?php endif; ?>
</div>
