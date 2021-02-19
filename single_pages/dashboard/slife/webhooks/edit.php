<?php
defined('C5_EXECUTE') or die('Access Denied.');

/** @var $form \Concrete\Core\Form\Service\Form */
/** @var $webhook \Slife\Entity\Webhook */
?>

<div class="ccm-dashboard-header-buttons">
    <?php
    if ($webhook->getId() && $webhook->getHandle() !== 'default') {
        ?>
        <a class="btn btn-danger"
           onclick="return confirm('<?php echo t('Do you want to delete this webhook and all its associated messages?'); ?>');"
           href="<?php echo $this->action('delete', $webhook->getId()); ?>">
            <?php echo t('Delete'); ?>
        </a>
        <?php
    }
    ?>
</div>

<div class="ccm-dashboard-content-inner">
    <form method="post" action="<?php echo $this->action('save'); ?>">
        <?php echo $token->output('slife_webhooks_form');?>

        <?php
        if ($webhook->getId()) {
            echo $form->hidden('id', $webhook->getId());
        }
        ?>

        <div class="form-group">
            <?php
            echo $form->checkbox('isEnabled', 1, $webhook->getIsEnabled()).' ';
            echo $form->label('isEnabled', t('Is enabled'));
            ?>
        </div>

        <div class="form-group">
            <?php
            echo $form->label('handle', t('Handle / Name').'*');
            echo $form->text('handle', $webhook->getHandle(), [
                'placeholder' => t('A unique identifier for this webhook'),
                'required' => 'required',
                'autofocus' => 'autofocus',
            ]);
            ?>
        </div>

        <div class="form-group">
            <?php
            echo $form->label('uri', t('URL to webhook').'*');
            echo $form->text('uri', $webhook->getUri(), [
                'placeholder' => t('The URL to the Slack endpoint'),
                'required' => 'required',
            ]);
            ?>
        </div>

        <div class="form-group">
            <?php
            echo $form->label('channel', t('Channel').'*');
            echo $form->text('channel', $webhook->getChannel(), [
                'placeholder' => '#general',
                'required' => 'required',
            ]);
            ?>
        </div>

        <div class="form-group">
            <?php
            echo $form->label('username', t('Bot name').'*');
            echo $form->text('username', $webhook->getUsername(), [
                'placeholder' => t('Slife'),
                'required' => 'required',
            ]);
            ?>
        </div>

        <?php
        $caption = $webhook->getId() ? t('Save') : t('Add');
        echo $form->submit('submit', $caption, ['class' => 'btn-primary']);
        ?>

        <a class="btn btn-default"
           href="<?php echo $this->action('view'); ?>">
            <?php echo t('Cancel'); ?>
        </a>
    </form>
</div>
