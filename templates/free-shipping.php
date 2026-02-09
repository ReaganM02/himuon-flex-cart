<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php if ($data['threshold'] > 0): ?>
    <section class="himuon-cart--progress <?php echo $data['progress'] >= 100 ? 'himuon-cart--progress-finish' : '' ?>">
        <p class="himuon-cart--progress-text">
            <?php
            if ($data['progress'] >= 100) {
                echo esc_html__('You have free shipping!', 'himuon-flex-cart');
            } else {
                printf(
                    esc_html__($data['remainingText']),
                    wp_kses_post($data['remaining'])
                );
            }
            ?>
        </p>
        <div class="himuon-cart--progress-bar"
             role="progressbar"
             aria-valuemin="0"
             aria-valuemax="100"
             aria-valuenow="<?php echo esc_attr((string) round($data['progress'])); ?>">
            <span class="himuon-cart--progress-fill"
                  style="width: <?php echo esc_attr((string) round($data['progress'])); ?>%;"></span>
        </div>
    </section>
<?php endif; ?>