<?php
defined('C5_EXECUTE') or die('Access Denied.');

/** @var $form \Concrete\Core\Form\Service\Form */
/** @var $slifeMessage \Slife\Entity\Message */
?>

<div class="ccm-dashboard-header-buttons">
    <?php
    if ($slifeMessage->getId()) {
        ?>
        <a class="btn btn-danger" href="<?php echo $this->action('delete', $slifeMessage->getId()); ?>">
            <?php echo t('Delete'); ?>
        </a>
        <?php
    }
    ?>
</div>

<div class="ccm-dashboard-content-inner">
    <form method="post" action="<?php echo $this->action('save'); ?>">
        <?php echo $token->output('slife_messages_form');?>

        <?php
        if ($slifeMessage->getId()) {
            echo $form->hidden('id', $slifeMessage->getId());
        }

        echo $form->hidden('event_id', $slifeMessage->getEvent()->getId());
        ?>

        <div class="form-group">
            <?php
            echo $form->label('event', t('Event').'*');
            echo $form->text('event', $slifeMessage->getEvent()->getEventHandle(), [
                'readonly' => 'readonly',
                'disabled' => 'disabled',
            ]);
            ?>
        </div>

        <div class="form-group">
            <?php
            echo $form->label('webhook_id', t('Webhook').'*');
            echo $form->select('webhook_id', $webhookOptions, $webhookValue, [
                'required' => 'required',
            ]);
            ?>
        </div>

        <div class="form-group">
            <?php
            echo $form->label('message', t('Message').'*');
            $value = $slifeMessage->getMessage();
            $value = ($value) ? $value : $slifeMessage->getEvent()->getDefaultMessage();
            echo $form->textarea('message', $value, [
                'placeholder' => $slifeMessage->getEvent()->getDefaultMessage(),
                'autofocus' => 'autofocus',
                'required' => 'required',
            ]);
            ?>
        </div>

        <div class="alert alert-info" role="alert">
            <?php
            if (!empty($placeholders)) {
                echo t('Placeholders you can use in the message text: %s.', $placeholders);
            }
            ?>

            <p>
                <?php
                echo t('Tip: <b>*asterisks*</b> and <i>_underscores_</i> are supported to style the message.');
                ?>
            </p>
        </div>

        <?php
        $caption = $slifeMessage->getId() ? t('Save') : t('Add');
        echo $form->submit('submit', $caption, ['class' => 'btn-primary']);
        ?>

        <a class="btn btn-default"
           href="<?php echo $this->action('view'); ?>">
            <?php echo t('Cancel'); ?>
        </a>
    </form>
</div>
