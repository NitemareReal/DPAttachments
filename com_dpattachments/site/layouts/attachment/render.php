<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2016 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

\defined('_JEXEC') or die();

use DigitalPeak\Component\DPAttachments\Administrator\Extension\DPAttachmentsComponent;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

$attachment = $displayData['attachment'];
if (!$attachment) {
	return;
}

/** @var \Joomla\CMS\Application\CMSApplicationInterface $app */
$app = $displayData['app'] ?? Factory::getApplication();
$app->getLanguage()->load('com_dpattachments', JPATH_ADMINISTRATOR . '/components/com_dpattachments');

$component = $app->bootComponent('dpattachments');
if (!$component instanceof DPAttachmentsComponent) {
	return;
}
$params = ComponentHelper::getParams('com_dpattachments');

$previewExtensions = [];
foreach (Folder::files(JPATH_SITE . '/components/com_dpattachments/tmpl/attachment') as $file) {
	$previewExtensions[] = File::stripExt($file);
}
$showWhen		= true;
$showWho		= true;
$showUploadDate	= $params->get('show_upload_date', 'allusers');
$showUploader	= $params->get('show_uploader', 'allusers');
$userLevels		= null;

if ($showUploadDate == "nouser") {
	$showWhen = false;
} else if ($showUploadDate == "selected") {// check if current user has any of the selected access level(s)
	// get selected access level for "show date"
	$levels = $params->get('show_creation_date_access_level', []);
	// get current user access level(s)
	$userLevels ??= $app->getIdentity()->getAuthorisedViewLevels();
	
	$showWhen = \count(\array_intersect($levels, $userLevels)) > 0;
}
if ($showUploader == "nouser") {
	$showWho = false;
} else if ($showUploader == "selected") {// check if current user has any of the selected access level(s)
	// get selected access level for "show uploader"
	$levels = $params->get('show_uploader_access_level', []);
	// get current user access level(s)
	$userLevels ??= $app->getIdentity()->getAuthorisedViewLevels();
	
	$showWho = \count(\array_intersect($levels, $userLevels)) > 0;
}
?>
<div class="dp-attachment">
	<?php if (in_array(strtolower(pathinfo((string) $attachment->path, PATHINFO_EXTENSION)), $previewExtensions)) { ?>
		<a href="<?php echo Route::link('site', 'index.php?option=com_dpattachments&view=attachment&tmpl=component&id=' . (int)$attachment->id); ?>"
		   class="dp-attachment__link">
			<?php echo $attachment->title; ?>
		</a>
	<?php } else { ?>
		<span class="dp-attachment__title"><?php echo $attachment->title; ?></span>
	<?php } ?>
	<span class="dp-attachment__size">[<?php echo $component->size($attachment->size); ?>]</span>
	<a href="<?php echo Route::_('index.php?option=com_dpattachments&task=attachment.download&id=' . (int)$attachment->id); ?>" target="_blank">
		<?php echo $component->renderLayout('block.icon', ['icon' => 'download']); ?>
	</a>
	<?php if (!empty($attachment->event) && !empty($attachment->event->afterDisplayTitle)) { ?>
		<div class="dp-attachment__after-title"><?php echo $attachment->event->afterDisplayTitle; ?></div>
	<?php } ?>
	<?php if (!empty($attachment->event) && !empty($attachment->event->beforeDisplayAttachment)) { ?>
		<div class="dp-attachment__before-display"><?php echo $attachment->event->beforeDisplayAttachment; ?></div>
	<?php } ?>
	<?php if ($showWhen || $showWho) : ?>
	<div class="dp-attachment__date">		
		<?php if ($showWhen && $showWho) : ?>
			<?php $author = $attachment->created_by_alias ?: ($attachment->author_name ?? $attachment->created_by); ?>
			<?php echo sprintf($app->getLanguage()->_('COM_DPATTACHMENTS_TEXT_UPLOADED_LABEL'), strtolower(HTMLHelper::_('date.relative', $attachment->created)), $author); ?>
		<?php elseif ($showWhen) : // only date ?>
			<?php echo sprintf($app->getLanguage()->_('COM_DPATTACHMENTS_TEXT_UPLOADED_ONLY_DATE_LABEL'), strtolower(HTMLHelper::_('date.relative', $attachment->created))); ?>
		<?php else : // only who ?>
			<?php $author = $attachment->created_by_alias ?: ($attachment->author_name ?? $attachment->created_by); ?>
			<?php echo sprintf($app->getLanguage()->_('COM_DPATTACHMENTS_TEXT_UPLOADED_ONLY_UPLOADER_LABEL'), $author); ?>
		<?php endif; ?>
	</div>
	<?php endif; ?>
	<?php if (!empty($attachment->event) && !empty($attachment->event->afterDisplayAttachment)) { ?>
		<div class="dp-attachment__after-display"><?php echo $attachment->event->afterDisplayAttachment; ?></div>
	<?php } ?>
	<div class="dp-attachment__actions">
		<?php if ($component->canDo('core.edit', $attachment->context, $attachment->item_id)) { ?>
			<a href="<?php echo Route::_('index.php?option=com_dpattachments&task=attachment.edit&id=' . $attachment->id . ($app->isClient('site') ? '&tmpl=component' : '')); ?>"
				class="dp-button dp-button-edit">
				<?php echo $component->renderLayout('block.icon', ['icon' => 'pencil']); ?>
				<?php echo $app->getLanguage()->_('JACTION_EDIT'); ?>
			</a>
		<?php } ?>
		<?php if ($component->canDo('core.edit.state', $attachment->context, $attachment->item_id)) { ?>
			<a href="<?php echo Route::_('index.php?option=com_dpattachments&task=attachment.publish&state=-2&id='
				. $attachment->id . '&' . Session::getFormToken() . '=1'); ?>" class="dp-button dp-button-trash">
				<?php echo $component->renderLayout('block.icon', ['icon' => 'trash']); ?>
				<?php echo $app->getLanguage()->_('JTRASH'); ?>
			</a>
		<?php } ?>
	</div>
</div>
