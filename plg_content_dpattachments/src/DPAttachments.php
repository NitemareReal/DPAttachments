<?php
/**
 * @package    DPAttachments
 * @copyright  Copyright (C) 2013 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace DigitalPeak\Plugin\Content\DPAttachments;

defined('_JEXEC') or die;

use DigitalPeak\Component\DPAttachments\Administrator\Extension\DPAttachmentsComponent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Registry\Registry;

class DPAttachments extends CMSPlugin
{
	protected $app;

	public function onContentAfterDisplay($context, $item, $params)
	{
		// Check if there is an ID
		if (empty($item->id)) {
			return '';
		}

		// Filter by category ids
		$catIds = $this->params->get('cat_ids');
		if (isset($item->catid) && !empty($catIds) && !in_array($item->catid, $catIds)) {
			return '';
		}

		// Mak the correct context
		if ($context === 'com_content.featured') {
			$context = 'com_content.article';
		}

		// Get the component instance
		$component = $this->app->bootComponent('dpattachments');
		if (!$component instanceof DPAttachmentsComponent) {
			return;
		}

		// Render the attachments and upload form
		$buffer = $component->render($context, $item->id, new Registry(['render.columns' => $this->params->get('column_count', 2)]));

		// Render the attachment form the original event as well
		if (isset($item->original_id) && $item->original_id > 0) {
			$buffer .= $component->render($context, $item->original_id, new Registry(['render.columns' => $this->params->get('column_count', 2)]), false);
		}

		return $buffer;
	}

	public function onContentAfterDelete($context, $item)
	{
		if (empty($item->id)) {
			return '';
		}

		if ($context === 'com_content.featured') {
			$context = 'com_content.article';
		}

		// Get the component instance
		$component = $this->app->bootComponent('dpattachments');
		if (!$component instanceof DPAttachmentsComponent) {
			return;
		}

		return $component->delete($context, $item->id);
	}
}
