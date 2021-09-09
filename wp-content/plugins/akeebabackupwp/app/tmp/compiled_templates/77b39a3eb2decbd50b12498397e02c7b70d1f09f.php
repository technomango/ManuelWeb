<?php /* /var/www/vhosts/soymanuel.com/httpdocs/wp-content/plugins/akeebabackupwp/app/Solo/ViewTemplates/Fsfilters/default.blade.php */ ?>
<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Awf\Text\Text;

defined('_AKEEBA') or die();

/** @var \Solo\View\Fsfilters\Html $this */
?>
<?php echo $this->loadAnyTemplate('CommonTemplates/ErrorModal'); ?>
<?php echo $this->loadAnyTemplate('CommonTemplates/ProfileName'); ?>

<form class="akeeba-form--inline akeeba-panel--info">
    <div class="akeeba-form-group">
		<label><?php echo \Awf\Text\Text::_('COM_AKEEBA_FILEFILTERS_LABEL_ROOTDIR'); ?></label>
		<span><?php echo $this->root_select; ?></span>
	</div>

    <div class="akeeba-form-group--actions">
        <button class="akeeba-btn--red" id="comAkeebaFileFiltersNuke">
            <span class="akion-ios-trash"></span>
			<?php echo \Awf\Text\Text::_('COM_AKEEBA_FILEFILTERS_LABEL_NUKEFILTERS'); ?>
        </button>
    </div>
</form>

<div id="ak_crumbs_container" class="akeeba-panel--100 akeeba-panel--information">
    <div>
        <ul id="ak_crumbs" class="akeeba-breadcrumb"></ul>
    </div>
</div>


<div class="akeeba-container--50-50">
    <div>
        <div class="akeeba-panel--info">
            <header class="akeeba-block-header">
                <h3>
					<?php echo \Awf\Text\Text::_('COM_AKEEBA_FILEFILTERS_LABEL_DIRS'); ?>
                </h3>
            </header>
            <div id="folders"></div>
        </div>
    </div>

    <div>
        <div class="akeeba-panel--info">
            <header class="akeeba-block-header">
                <h3>
					<?php echo \Awf\Text\Text::_('COM_AKEEBA_FILEFILTERS_LABEL_FILES'); ?>
                </h3>
            </header>
            <div id="files"></div>
        </div>
    </div>
</div>

<div class="clear"></div>
