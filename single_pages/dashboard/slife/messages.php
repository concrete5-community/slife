<?php
defined('C5_EXECUTE') or die('Access Denied.');

/** @var $form \Concrete\Core\Form\Service\Form */
?>

<?php
if (count($eventOptions) > 1) {
    ?>
    <div class="ccm-dashboard-header-buttons">
        <form method="post" class="form-inline" action="<?php echo $this->action('add'); ?>">
            <?php
            echo $form->select('event_id', $eventOptions, [
                'required' => 'required',
            ]);
            ?>
            <?php
            echo $form->submit('submit', t('Add message'), ['class' => 'btn-primary']);
            ?>
        </form>
    </div>
    <?php
}
?>

<div class="ccm-dashboard-content-full-inner">
    <p>
        <?php
        echo t("Messages are texts send to Slack. They can be customized per event. 
        Each event can supports different placeholders. For example, '{username}' can be 
        replaced by 'John Doe'.<br>You can add more events by installing Slife integrations or using custom code.");
        ?>
    </p>
</div>

<div class="ccm-dashboard-content-full">
    <?php
    /** @var $items \Slife\Entity\Message[] */
    if (count($items)) {
        ?>
        <div class="table-responsive">
            <table border="0" cellspacing="0" cellpadding="0" class="ccm-search-results-table">
                <thead>
                    <th><span><?php echo t('Event'); ?></span></th>
                    <th><span><?php echo t('Message'); ?></span></th>
                    <th><span><?php echo t('Webhook'); ?></span></th>
                </thead>
                <tbody>
                    <?php
                    foreach ($items as $message) {
                        ?>
                        <tr data-id="<?php echo $message->getId() ?>">
                            <td><?php echo $message->getEvent()->getEventHandle() ?></td>
                            <td><?php echo h($message->getMessage()); ?></td>
                            <td>
                                <a href="<?php echo $this->url('/dashboard/slife/webhooks/edit/', $message->getWebhook()->getId()); ?>">
                                <?php echo $message->getWebhook()->getHandle(); ?>
                                </a>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>

            <script>
                $(function() {
                    $('.ccm-search-results-table tr')
                        .on('click', function() {
                            window.location.href = '<?php echo $this->action('edit'); ?>' + '/' + $(this).data('id');
                        }).on('mouseover', function(){
                        $(this).addClass('ccm-search-select-hover');
                    }).on('mouseout', function() {
                        $(this).removeClass('ccm-search-select-hover');
                    });
                });
            </script>
        </div>
        <?php
    }
    ?>

    <?php
    if (count($eventOptions) < 2) {
        ?>
        <div class="ccm-dashboard-content-full-inner">
            <div class="alert alert-warning" role="alert">
                <?php
                echo t('
                    No messages or events have been added. 
                    Please install one of the Slife integrations.
                ');
                ?>
            </div>
        </div>
        <?php
    } elseif (count($items) === 0) {
        ?>
        <div class="ccm-dashboard-content-full-inner">
            <div class="alert alert-warning" role="alert">
                <?php echo t('No messages have been added yet.'); ?>
            </div>
        </div>
        <?php
    }
    ?>
</div>
