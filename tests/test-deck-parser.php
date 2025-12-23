<?php
/**
 * Class DeckParserTest
 *
 * @package Magic_The_Gathering_Card_Tooltips
 */

/**
 * Test case for deck parsing functionality.
 */
class DeckParserTest extends WP_UnitTestCase {

	private $plugin;

	public function setUp(): void {
		parent::setUp();
		$this->plugin = new Deckbox_Tooltip_plugin();
	}

	/**
	 * Test parsing deck with multiple categories.
	 */
	public function test_parse_multiple_categories() {
		$lines = array(
			'Creatures',
			'4 Tarmogoyf',
			'2 Snapcaster Mage',
			'Spells',
			'4 Lightning Bolt',
			'Lands',
			'10 Island',
			'Sideboard',
			'3 Blood Moon'
		);

		$result = $this->plugin->parse_deck_structure( $lines );

		$this->assertCount( 4, $result );

		// Check categories and card counts
		$this->assertEquals( 'Creatures', $result[0]['name'] );
		$this->assertCount( 2, $result[0]['cards'] );
		$this->assertEquals( 4, $result[0]['cards'][0]['count'] );
		$this->assertEquals( 'Tarmogoyf', $result[0]['cards'][0]['name'] );

		$this->assertEquals( 'Spells', $result[1]['name'] );
		$this->assertEquals( 'Lands', $result[2]['name'] );
		$this->assertEquals( 'Sideboard', $result[3]['name'] );
	}

	/**
	 * Test parsing cards with apostrophes and no category.
	 */
	public function test_parse_cards_with_apostrophes_and_no_category() {
		$lines = array(
			'4 Urza\'s Saga',
			'2 Mishra\'s Factory',
			'1 Black Lotus'
		);

		$result = $this->plugin->parse_deck_structure( $lines );

		$this->assertCount( 1, $result );
		$this->assertEquals( '', $result[0]['name'] );
		$this->assertCount( 3, $result[0]['cards'] );
		$this->assertEquals( 'Urza\'s Saga', $result[0]['cards'][0]['name'] );
		$this->assertEquals( 1, $result[0]['cards'][2]['count'] );
	}

	/**
	 * Test parsing cards with set codes (MTG Arena format).
	 */
	public function test_parse_cards_with_set_codes() {
		$lines = array(
			'1 Adventurer\'s Inn (FIN) 271',
			'1 Airship Crash (FIN) 171',
			'Aerith Rescue Mission (FIN)'
		);

		$result = $this->plugin->parse_deck_structure( $lines );

		$this->assertCount( 1, $result );
		$this->assertCount( 3, $result[0]['cards'] );

		// Should strip set codes and numbers
		$this->assertEquals( 1, $result[0]['cards'][0]['count'] );
		$this->assertEquals( 'Adventurer\'s Inn', $result[0]['cards'][0]['name'] );

		$this->assertEquals( 1, $result[0]['cards'][1]['count'] );
		$this->assertEquals( 'Airship Crash', $result[0]['cards'][1]['name'] );

		// Card without count should default to 1
		$this->assertEquals( 1, $result[0]['cards'][2]['count'] );
		$this->assertEquals( 'Aerith Rescue Mission', $result[0]['cards'][2]['name'] );
	}

	/**
	 * Test that title with HTML/script is properly escaped.
	 */
	public function test_deck_title_escapes_html() {
		$content = '[mtg_deck title="My Deck\'s & Yours <script>alert(\'xss\')</script>"]
3 Lightning Bolt
2 Mountain
[/mtg_deck]';

		$output = do_shortcode( $content );

		// Title should be escaped (HTML entities)
		$this->assertStringContainsString( 'My Deck&#039;s &amp; Yours', $output );
		$this->assertStringContainsString( '&lt;script&gt;', $output ); // Script tag is escaped
		$this->assertStringNotContainsString( '<script>alert', $output ); // Not executable

		// Cards should still render normally
		$this->assertStringContainsString( 'href="https://deckbox.org/mtg/Lightning Bolt"', $output );
		$this->assertStringContainsString( 'href="https://deckbox.org/mtg/Mountain"', $output );
	}
}
