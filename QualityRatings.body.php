<?php

$wgQualityRatings = array(
	'Tattered',
	'Fine',
	'Superior',
	'Exceptional',
	'Masterwork',
	-1 => 'Unknown',
);

class QualityRatingHandler {
	private static $ratingCache = array();
	private static $defaultLogEntry = array(
		'rating' => -1,
		'user' => 'Unknown user',
	);
	public static function toTitle ($title) {
		if (!($title instanceof Title)) {
			$title = Title::newFromText((string)$title);
		}
		return $title;
	}
	public static function isValidRatingData ($text) {
		return !!json_decode($text);
	}
	public static function textToRatingData ($text) {
		$data = json_decode($text, true);   // Return objects as arrays
		if (!is_array($data)) {
			// Log does not exist
			return array(self::$defaultLogEntry);
		}
		foreach ($data as $k => $d) {
			if (!is_array($d)) {
				$data[$k] = self::$defaultLogEntry;
			}
		}
		return $data;
	}
	public static function getRatingName ($rating) {
		global $wgQualityRatings;
		return $wgQualityRatings[$rating];
	}
	public static function getRatingData ($title) {
		$title = self::toTitle($title);
		$title = Title::newFromText($title->getPrefixedDBkey() . '/rating_log');
		$content = WikiPage::factory($title)->getText();
		return self::textToRatingData($content);
	}
	public static function getRating ($title) {
		$title = self::toTitle($title);
		$dbkey = $title->getPrefixedDBkey();
		if (!array_key_exists($dbkey, self::$ratingCache)) {
			$data = self::getRatingData($title);
			$data = end($data);
			self::$ratingCache[$dbkey] = $data['rating'];
		}
		return self::$ratingCache[$dbkey];
	}
	public static function setRating ($title, $rating) {
		global $wgUser;
		$title = self::toTitle($title);
		$data = self::getRatingData($title);
		$data[] = array(
			'rating' => $rating,
			'user' => $wgUser->getName(),
		);
		$content = json_encode($data);
		$logTitle = Title::newFromText($title->getPrefixedDBkey() . '/rating_log');
		$log = WikiPage::factory($logTitle);
		$log->doEdit(
			$content,
			'Updating log',
			EDIT_SUPPRESS_RC,
			false,  # baseRevId
			User::newFromName('Rating script')
		);
	}
	public static function generateLogHTML ($text) {
		$data = self::textToRatingData($text);
		$data = array_reverse($data);
		$output = '';
		foreach ($data as $logEntry) {
			$output .= '*' . self::getRatingName($logEntry['rating']);
			$output .= "\n";
		}
		return $output;
	}
}


class QualityRatingHooks {
	public static function includeModules ($outPage) {
		$outPage->addModules('ext.QualityRatings');
		return true;
	}
	public static function getReservedUsernames (&$reservedUsernames) {
		$reservedUsernames[] = 'Rating script';
		return true;
	}
	public static function onParserBeforeStrip ($parser, &$text, &$strip_state) {
		$titleParts = explode('/', $parser->getTitle()->getFullText());
		$subpageText = $titleParts[count($titleParts) - 1];
		if ($subpageText != 'rating log')
			return true;
		if (!QualityRatingHandler::isValidRatingData($text))
			return true;
		$text = QualityRatingHandler::generateLogHTML($text);
		return true;
	}
}
