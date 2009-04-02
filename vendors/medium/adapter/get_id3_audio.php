<?php
/**
 * GetId3 Audio Medium Adapter File
 *
 * Copyright (c) 2007-2009 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.2
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
 * @copyright  2007-2009 David Persson <davidpersson@qeweurope.org>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */
/**
 * GetId3 Audio Medium Adapter Class
 *
 * @package    media
 * @subpackage media.libs.medium.adapter
 * @link       http://getid3.sourceforge.net/
 */
class GetId3AudioMediumAdapter extends MediumAdapter {
	var $require = array(
					'mimeTypes' => array(
									'audio/ogg',
									'audio/mpeg',
									'audio/ms-wma',
									'audio/ms-asf',
									'audio/realaudio',
									'audio/pn-realaudio',
									'audio/pn-multirate-realaudio',
									'audio/wav',
									'audio/riff',
									'audio/wavpack',
									'audio/musepack', /* MPC */
									'audio/aac',
									'audio/mp4', /* AAC */
									'audio/m4a', /* AAC */
									'audio/m4b', /* AAC */
									'audio/ac3',
									'audio/aiff',
									'audio/ape',
									'audio/shorten',
									'audio/basic',
									'audio/midi',
									'audio/flac',
									'audio/voc',
									'audio/s3m',
									'audio/xm',
									'audio/it',
									'audio/mod',
									'audio/matroska',
									/* Not in freedesktop.org database */
									'audio/pac',
									'audio/bonk',
									'audio/dts',
									'audio/cda',
									/*
									 * Will not be used since audio Medium can't have
									 * application/octet-stream mime type, this is a reminder.
									 *
									 * LA (Lossless Audio), OptimFROG, TTA, LiteWave,
									 * RKAU, AVR (Audio Visual Research)
									 */
									'application/octet-stream',
									),
							'imports' => array(
											array('type' => 'Vendor','name' => 'getID3', 'file' => 'getid3/getid3.php')
											),
							'extensions' => array('gd'),
							);

	function initialize(&$Medium) {
		if (isset($Medium->objects['getID3'])) {
			return true;
		}

		if (!isset($Medium->file)) {
			return false;
		}

		$Object = new getID3();
		$Object->encoding = 'UTF-8';
		$Object->analyze($Medium->file);

		if (isset($Object->info['error'])) {
			return false;
		}

		getid3_lib::CopyTagsToComments($Object->info);

		$Medium->objects['getID3'] =& $Object;

		return true;
	}

	function artist(&$Medium) {
		if (isset($Medium->objects['getID3']->info['comments']['artist'][0])) {
			return $Medium->objects['getID3']->info['comments']['artist'][0];
		}
		return false;
	}

	function title(&$Medium) {
		if (isset($Medium->objects['getID3']->info['comments']['title'][0])) {
			return $Medium->objects['getID3']->info['comments']['title'][0];
		}
		return false;
	}

	function album(&$Medium) {
		if (isset($Medium->objects['getID3']->info['comments']['album'][0])) {
			return $Medium->objects['getID3']->info['comments']['album'][0];
		}
		return false;
	}

	function year(&$Medium) {
		if (isset($Medium->objects['getID3']->info['comments']['year'][0])) {
			return $Medium->objects['getID3']->info['comments']['year'][0];
		}
		return false;
	}

	function duration(&$Medium) {
		if (isset($Medium->objects['getID3']->info['playtime_seconds'])) {
			return $Medium->objects['getID3']->info['playtime_seconds'];
		}
		return false;
	}

	function track(&$Medium) {
		if (isset($Medium->objects['getID3']->info['comments']['track_number'][0])) {
			return $Medium->objects['getID3']->info['comments']['track_number'][0];
		}
		if (isset($Medium->objects['getID3']->info['comments']['tracknumber'][0])) {
			return $Medium->objects['getID3']->info['comments']['tracknumber'][0];
		}
		return false;
	}

	function samplingRate(&$Medium) {
		if (isset($Medium->objects['getID3']->info['audio']['sample_rate'])) {
			return $Medium->objects['getID3']->info['audio']['sample_rate'];
		}
	}

	function bitrate(&$Medium) {
		if (isset($Medium->objects['getID3']->info['bitrate'])) {
			return $Medium->objects['getID3']->info['bitrate'];
		}
	}

	function convert(&$Medium, $mimeType) {
		if (Medium::name(null, $mimeType) === 'Image') {
			$coverArt = $this->__coverArt($Medium);

			if (!$coverArt) {
				return false;
			}

			$resource = @imagecreatefromstring($coverArt);

			if (!is_resource($resource)) {
				return false;
			}

			$Image = Medium::factory(array('gd' => $resource), 'image/gd');
			return $Image->convert($mimeType);
		}
		return false;
	}

	function __coverArt(&$Medium) {
		if (!empty($Medium->objects['getID3']->info['id3v2']['APIC'][0]['data'])) {
			return $Medium->objects['getID3']->info['id3v2']['APIC'][0]['data'];
		}
		if (!empty($Medium->objects['getID3']->info['id3v2']['PIC'][0]['data'])) {
			return $Medium->objects['getID3']->info['id3v2']['PIC'][0]['data'];
		}
		if (!empty($Medium->objects['getID3']->info['flac']['PICTURE'][0]['image_data'])) {
			return $Medium->objects['getID3']->info['flac']['PICTURE'][0]['image_data'];
		}
		if (!empty($Medium->objects['getID3']->info['vorbiscomment']['coverart'][0])) {
			return base64_decode($Medium->objects['getID3']->info['vorbiscomment']['coverart'][0]);
		}
		return false;
	}
}
?>