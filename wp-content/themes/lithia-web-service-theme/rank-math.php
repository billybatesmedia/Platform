<?php
/**
 * Rank Math editor customizations for Lithia Web.
 *
 * @package LithiaWebServiceTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'rank_math/researches/tests',
	static function ( $tests, $type ) {
		if ( 'post' !== $type ) {
			return $tests;
		}

		$post = get_post();

		if ( ! $post instanceof WP_Post ) {
			return $tests;
		}

		// Service and page titles should stay clear and credible, not clickbait-heavy.
		if ( in_array( $post->post_type, [ 'page', 'services' ], true ) ) {
			unset(
				$tests['titleSentiment'],
				$tests['titleHasPowerWords']
			);
		}

		// Some marketing pages are rendered mostly from templates/options, so body-content tests are misleading.
		if ( 'page' === $post->post_type && '' === trim( wp_strip_all_tags( (string) $post->post_content ) ) ) {
			unset(
				$tests['contentHasTOC'],
				$tests['contentHasShortParagraphs'],
				$tests['keywordIn10Percent'],
				$tests['keywordInContent'],
				$tests['keywordInSubheadings'],
				$tests['keywordDensity'],
				$tests['lengthContent'],
				$tests['linksHasInternal'],
				$tests['linksHasExternals'],
				$tests['linksNotAllExternals']
			);
		}

		return $tests;
	},
	20,
	2
);
