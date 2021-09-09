<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var  AngieViewMain  $this */

defined('_AKEEBA') or die();

echo $this->loadAnyTemplate('steps/steps', array(
	'helpurl' => 'https://www.akeeba.com/documentation/solo/angie-wordpress.html#angie-wordpress-first',
	'videourl' => 'https://www.akeeba.com/videos/1215-akeeba-backup-wordpress/1651-abtw05-restoring-site-new-server.html'
));
?>

<?php if (!$this->reqMet): ?>
<div class="akeeba-block--failure">
	<?php echo AText::_('MAIN_LBL_REQUIREDREDTEXT'); ?>
</div>
<?php endif; ?>

<div class="akeeba-container--50-50">
	<?php echo $this->loadAnyTemplate('init/panel_required', []); ?>
	<?php echo $this->loadAnyTemplate('init/panel_recommended', []); ?>
</div>

<div class="akeeba-container--50-50">
	<?php echo $this->loadAnyTemplate('init/panel_backupinfo', []); ?>
	<?php echo $this->loadAnyTemplate('init/panel_serverinfo', []); ?>
</div>
