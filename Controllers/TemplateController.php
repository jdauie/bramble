<?php

namespace Jacere\Bramble\Controllers;

use Jacere\Bramble\Core\Controller;
use Jacere\Skhema\TemplateManager;
use Jacere\Subvert\Subvert;

abstract class TemplateController extends Controller {

	/** @var TemplateManager */
	protected $m_manager;

	public function before() {
		parent::before();

		$this->m_manager = TemplateManager::create(BRAMBLE_TEMPLATES);

		$this->m_manager->register('subvert', function($options, $context, $text) {
			$subvert_options = [];
		
			$subvert_options['image_callback'] = function(&$attributes) {
				// add dimensions to url
				$width = isset($attributes['width']) ? $attributes['width'] : '0';
				$height = isset($attributes['height']) ? $attributes['height'] : '0';
		
				if (ctype_digit($width) && ctype_digit($height) && ($width != 0 || $height != 0)) {
					$parts = pathinfo($attributes['src']);
					$attributes['src'] = sprintf('%s/%s-%sx%s.%s', $parts['dirname'], $parts['filename'], $width, $height, $parts['extension']);
				}
			};
		
			if ($options) {
				if (isset($options['code'])) {
					$subvert_options['code_formatting'] = true;
				}
				if (isset($options['root'])) {
					$subvert_options['root_url'] = BRAMBLE_URL;
				}
				if (isset($options['header'])) {
					$subvert_options['header_level'] = (int)$options['header'];
				}
				else {
					// does it make sense to have this here?
					$subvert_options['header_level'] = 3;
				}
			}
			return Subvert::Parse($text, $subvert_options);
		});

		$this->m_manager->register('format-date', function($options, $context, $date) {
			if (is_string($date)) {
				$date = strtotime($date);
			}
			if ($options) {
				if (isset($options['atom'])) {
					$date = date(\DateTime::ATOM, $date);
				}
				else if (count($options) === 1) {
					$date = date(trim(key($options), "'"), $date);
				}
			}
			else {
				$date = date('Y-m-d', $date);
			}
			return $date;
		});

		$this->m_manager->register('format-url', function($options, $context) {
			if ($options && count($options) === 1) {
				if (isset($options['post'])) {
					$time = $context['time'];
					if (is_string($time)) {
						$time = strtotime($time);
					}
					return self::_format_post_url($time, $context['slug']);
				}
				else if (isset($options['page'])) {
					return self::_format_page_url($context['slug']);
				}
				else if (isset($options['category'])) {
					return self::_format_category_url($context['slug']);
				}
				else if (isset($options['archive'])) {
					$time = $context['month'];
					if (is_string($time)) {
						$time = strtotime($time);
					}
					return self::_format_archive_url($time);
				}
			}
			return '';
		});
	}

	public static function _format_category_url($slug) {
		return sprintf('%scategory/%s', BRAMBLE_URL, $slug);
	}

	public static function _format_archive_url($time) {
		return sprintf('%s%s', BRAMBLE_URL, date('Y/m', $time));
	}

	public static function _format_post_url($time, $slug) {
		return sprintf('%s%s/%s', BRAMBLE_URL, date('Y/m', $time), $slug);
	}

	public static function _format_page_url($slug) {
		return sprintf('%s%s', BRAMBLE_URL, $slug);
	}
}
