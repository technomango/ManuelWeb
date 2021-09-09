<?php /* /var/www/vhosts/mango.com.gt/httpdocs/demos/soymanu/wp-content/plugins/akeebabackupwp/app/Solo/ViewTemplates/Update/default.blade.php */ ?>
<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Awf\Text\Text;

defined('_AKEEBA') or die();

/** @var   \Solo\View\Update\Html  $this */

$releaseNotes = $this->updateInfo->get('releasenotes');
$infoUrl = $this->updateInfo->get('infourl');
$requirePlatformName = $this->getContainer()->segment->get('platformNameForUpdates', 'php');

?>

<?php if(!empty($releaseNotes)): ?>
<div class="modal fade" id="releaseNotesPopup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none">
    <div class="akeeba-renderer-fef <?php echo ($this->getContainer()->appConfig->get('darkmode', -1) == 1) ? 'akeeba-renderer-fef--dark' : ''; ?>">
        <h4 class="modal-title" id="myModalLabel">
			<?php echo \Awf\Text\Text::_('SOLO_UPDATE_RELEASENOTES'); ?>
        </h4>
        <div>
            <p>
		        <?php echo $releaseNotes; ?>

            </p>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if($this->needsDownloadId): ?>
<div id="solo-error-update-nodownloadid" class="akeeba-block--failure">
	<p>
		<?php echo \Awf\Text\Text::_('SOLO_UPDATE_ERROR_NEEDSAUTH'); ?>
	</p>
</div>
<?php endif; ?>

<?php if(!$this->updateInfo->get('loadedUpdate', 1)): ?>
	<div class="akeeba-block--failure" id="solo-error-update-noconnection">
		<h3>
			<?php echo \Awf\Text\Text::_('SOLO_UPDATE_NOCONNECTION_HEAD'); ?>
		</h3>
		<p>
			<?php echo \Awf\Text\Text::sprintf('SOLO_UPDATE_NOCONNECTION_BODY', $this->getModel()->getUpdateStreamURL()); ?>
		</p>
	</div>
<?php elseif($this->updateInfo->get('hasUpdate', 0)): ?>
	<div class="akeeba-block--warning" id="solo-warning-update-found">
		<h3>
			<?php echo \Awf\Text\Text::_('SOLO_UPDATE_HASUPDATES_HEAD'); ?>
		</h3>
	</div>
<?php elseif(!$this->updateInfo->get('minstabilityMatch', 0)): ?>
	<div class="akeeba-block--info" id="solo-error-update-minstability">
		<h3>
			<?php echo \Awf\Text\Text::_('SOLO_UPDATE_MINSTABILITY_HEAD'); ?>
		</h3>
	</div>
<?php elseif(!$this->updateInfo->get('platformMatch', 0)): ?>
	<div class="akeeba-block--failure" id="solo-error-update-platform-mismatch">
		<h3>
			<?php if(empty($requirePlatformName) || ($requirePlatformName == 'php')): ?>
				<?php echo \Awf\Text\Text::_('SOLO_UPDATE_PLATFORM_HEAD'); ?>
			<?php elseif($requirePlatformName == 'wordpress'): ?>
				<?php echo \Awf\Text\Text::_('SOLO_UPDATE_WORDPRESS_PLATFORM_HEAD'); ?>
			<?php elseif($requirePlatformName == 'joomla'): ?>
				<?php echo \Awf\Text\Text::_('SOLO_UPDATE_JOOMLA_PLATFORM_HEAD'); ?>
			<?php endif; ?>
		</h3>
	</div>
<?php else: ?>
	<div class="akeeba-block--success" id="solo-success-update-uptodate">
		<h3>
			<?php echo \Awf\Text\Text::_('SOLO_UPDATE_NOUPDATES_HEAD'); ?>
		</h3>
	</div>
<?php endif; ?>

<table class="liveupdate-infotable akeeba-table--striped">
    <tr>
        <td><?php echo \Awf\Text\Text::_('SOLO_UPDATE_CURRENTVERSION'); ?></td>
        <td>
			<span class="akeeba-label--info">
				<?php echo AKEEBABACKUP_VERSION; ?>

			</span>
        </td>
    </tr>
    <tr>
        <td><?php echo \Awf\Text\Text::_('SOLO_UPDATE_LATESTVERSION'); ?></td>
        <td>
			<span class="akeeba-label--success">
				<?php echo $this->updateInfo->get('version'); ?>

			</span>
        </td>
    </tr>
    <tr>
        <td><?php echo \Awf\Text\Text::_('SOLO_UPDATE_LATESTRELEASED'); ?></td>
        <td><?php echo $this->updateInfo->get('date'); ?></td>
    </tr>
    <tr>
        <td><?php echo \Awf\Text\Text::_('SOLO_UPDATE_DOWNLOADURL'); ?></td>
        <td>
            <a href="<?php echo $this->updateInfo->get('link'); ?>">
				<?php echo $this->escape($this->updateInfo->get('link')); ?>

            </a>
        </td>
    </tr>
	<?php if(!empty($releaseNotes) || !empty($infoUrl)): ?>
        <tr>
            <td><?php echo \Awf\Text\Text::_('SOLO_UPDATE_RELEASEINFO'); ?></td>
            <td>
				<?php if(!empty($releaseNotes)): ?>
                    <a href="#" id="btnLiveUpdateReleaseNotes">
						<?php echo \Awf\Text\Text::_('SOLO_UPDATE_RELEASENOTES'); ?>
                    </a>
				<?php endif; ?>

				<?php if(!empty($releaseNotes) && !empty($infoUrl)): ?>
                    &nbsp;&bull;&nbsp;
				<?php endif; ?>

				<?php if(!empty($infoUrl)): ?>
                    <a href="<?php echo $infoUrl; ?>" target="_blank" class="btn btn-link">
						<?php echo \Awf\Text\Text::_('SOLO_UPDATE_READMOREINFO'); ?>
                    </a>
				<?php endif; ?>
            </td>
        </tr>
	<?php endif; ?>
</table>

<p>
	<?php if($this->updateInfo->get('hasUpdate', 0)): ?>
		<?php $disabled = $this->needsDownloadId ? 'disabled="disabled"' : '' ?>
		<a <?php echo $disabled; ?>

			href="<?php echo $this->container->router->route('index.php?view=update&task=download'); ?>"
			class="akeeba-btn--large--primary">
			<span class="akion-chevron-right"></span>
			<?php echo \Awf\Text\Text::_('SOLO_UPDATE_DO_UPDATE'); ?>
		</a>
	<?php endif; ?>
	<a href="<?php echo $this->container->router->route('index.php?view=update&force=1'); ?>"
		class="akeeba-btn--grey">
		<span class="akion-refresh"></span>
		<?php echo \Awf\Text\Text::_('SOLO_UPDATE_REFRESH_INFO'); ?>
	</a>
</p>
