<?php  
defined('C5_EXECUTE') or die('Access Denied.');

/** @var $entity \Concrete\Core\Entity\Express\Entity */
?>

<div class="ccm-dashboard-header-buttons">
    <a href="<?php echo $this->action('add') ?>" class="btn btn-primary">
        <?php echo t('Add Webhook') ?>
    </a>
</div>

<div class="ccm-dashboard-content-full-inner">
    <p>
        <?php
        echo t("Webhooks are a simple way to post messages from external sources into Slack. 
        They make use of normal HTTP requests with a JSON payload, 
        which includes the message and a few other optional details described later. 
        You can add multiple webhooks, e.g. to send notifications to different Slack channels.");
        ?>
    </p>
    <br>
</div>

<div class="ccm-dashboard-content-full">
    <?php
    /** @var $items \Slife\Entity\Webhook[] */
    if (count($items)) {
        ?>
        <div class="table-responsive">
            <table border="0" cellspacing="0" cellpadding="0" class="ccm-search-results-table">
                <thead>
                    <th><span><?php echo t('Handle'); ?></span></th>
                    <th><span><?php echo t('Channel'); ?></span></th>
                    <th><span><?php echo t('Bot name'); ?></span></th>
                    <th><span>&nbsp;</span></th>
                </thead>
                <tbody>
                    <?php
                    foreach ($items as $webhook) {
                        ?>
                        <tr data-id="<?php echo $webhook->getId() ?>">
                            <td><?php echo $webhook->getHandle() ? h($webhook->getHandle()) : '-'; ?></td>
                            <td><?php echo h($webhook->getChannel()); ?></td>
                            <td><?php echo h($webhook->getUsername()); ?></td>
                            <td style="height: 70px">
                                <?php
                                if ($webhook->isEnabled() && !empty($webhook->getUri())) {
                                    ?>
                                    <a href="<?php echo $this->action('test', $webhook->getId()); ?>"
                                       class="btn btn-success">
                                        <?php echo t('Send test message'); ?>
                                    </a>
                                    <?php
                                }

                                if ($webhook->isDisabled()) {
                                    ?>
                                    <span class="label label-warning" style="padding: 7px 10px;">Disabled</span>
                                    <?php
                                }

                                if (empty($webhook->getUri())) {
                                    ?>
                                    <span class="label label-danger" style="padding: 7px 10px;">URL missing</span>
                                    <?php
                                }

                                ?>
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
    } else {
        ?>
        <div class="ccm-dashboard-content-full-inner">
            <p><?php echo t('No webhook has been created yet.') ?></p>
        </div>
        <?php
    }
    ?>
</div>
