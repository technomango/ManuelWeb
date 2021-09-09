<?php /* /var/www/vhosts/mango.com.gt/httpdocs/demos/soymanu/wp-content/plugins/akeebabackupwp/app/Solo/ViewTemplates/Manage/howtorestore_modal.blade.php */ ?>
<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Awf\Text\Text;

defined('_AKEEBA') or die();

/** @var $this \Solo\View\Configuration\Html */

$router = $this->container->router;

$proKey = (defined('AKEEBABACKUP_PRO') && AKEEBABACKUP_PRO) ? 'PRO' : 'CORE';

$js = <<< JS

akeeba.System.documentReady(function(){
	setTimeout(function() {
        akeeba.System.howToRestoreModal = akeeba.Modal.open({
            inherit: '#akeeba-config-howtorestore-bubble',
            width: '80%'
        });		
	}, 500);
});

JS;
?>
<?php $this->container->application->getDocument()->addScriptDeclaration($js); ?>

<div id="akeeba-config-howtorestore-bubble" style="display: none;">
    <div class="akeeba-renderer-fef <?php echo ($this->getContainer()->appConfig->get('darkmode', -1) == 1) ? 'akeeba-renderer-fef--dark' : ''; ?>">
        <h4>
		    <?php echo \Awf\Text\Text::_('COM_AKEEBA_BUADMIN_LABEL_HOWDOIRESTORE_LEGEND'); ?>
        </h4>

        <p>
            <?php echo \Awf\Text\Text::sprintf('COM_AKEEBA_BUADMIN_LABEL_HOWDOIRESTORE_TEXT_' . $proKey,
            'https://www.akeeba.com/videos/1214-akeeba-solo/1637-abts05-restoring-site-new-server.html',
            $router->route('index.php?view=Transfer'),
            'https://www.akeeba.com/latest-kickstart-core.zip'
            ); ?>
        </p>
        <?php if(!AKEEBABACKUP_PRO): ?>
            <p>
                <?php if($this->getContainer()->segment->get('insideCMS', false)): ?>
                    <?php echo \Awf\Text\Text::sprintf('COM_AKEEBA_BUADMIN_LABEL_HOWDOIRESTORE_TEXT_CORE_INFO_ABOUT_PRO',
                    'https://www.akeeba.com/products/akeeba-backup-wordpress.html'); ?>
                <?php else: ?>
                    <?php echo \Awf\Text\Text::sprintf('COM_AKEEBA_BUADMIN_LABEL_HOWDOIRESTORE_TEXT_CORE_INFO_ABOUT_PRO',
                    'https://www.akeeba.com/products/akeeba-solo.html'); ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <div>
            <a href="#" onclick="akeeba.System.howToRestoreModal.close(); document.getElementById('akeeba-config-howtorestore-bubble').style.display = 'none'" class="akeeba-btn--primary">
                <span class="akion-close"></span>
		        <?php echo \Awf\Text\Text::_('COM_AKEEBA_BUADMIN_BTN_REMINDME'); ?>
            </a>
            <a href="<?php echo $this->container->router->route('index.php?view=Manage&task=hideModal'); ?>" class="akeeba-btn--green">
                <span class="akion-checkmark-circled"></span>
		        <?php echo \Awf\Text\Text::_('COM_AKEEBA_BUADMIN_BTN_DONTSHOWTHISAGAIN'); ?>
            </a>
        </div>
    </div>
</div>
