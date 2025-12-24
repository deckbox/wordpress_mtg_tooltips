<?php
/**
 * Class DeckParserTest
 *
 * @package Magic_The_Gathering_Card_Tooltips
 */

class DeckParserTest extends WP_UnitTestCase {

	private $plugin;

	public function setUp(): void {
		parent::setUp();
		$this->plugin = new Deckbox_Tooltip_plugin();
	}

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

public function test_parse_cards_with_set_codes() {
		$lines = array(
			'1 Adventurer\'s Inn (FIN) 271',
			'1 Airship Crash (FIN) 171',
			'3 Aerith Rescue Mission (FIN)'
		);

		$result = $this->plugin->parse_deck_structure( $lines );

		$this->assertCount( 1, $result );
		$this->assertCount( 3, $result[0]['cards'] );

		// Should parse name, set, and nr separately
		$this->assertEquals( 1, $result[0]['cards'][0]['count'] );
		$this->assertEquals( 'Adventurer\'s Inn', $result[0]['cards'][0]['name'] );
		$this->assertEquals( 'FIN', $result[0]['cards'][0]['set'] );
		$this->assertEquals( '271', $result[0]['cards'][0]['nr'] );

		$this->assertEquals( 1, $result[0]['cards'][1]['count'] );
		$this->assertEquals( 'Airship Crash', $result[0]['cards'][1]['name'] );
		$this->assertEquals( 'FIN', $result[0]['cards'][1]['set'] );
		$this->assertEquals( '171', $result[0]['cards'][1]['nr'] );

		// Set without number
		$this->assertEquals( 3, $result[0]['cards'][2]['count'] );
		$this->assertEquals( 'Aerith Rescue Mission', $result[0]['cards'][2]['name'] );
		$this->assertEquals( 'FIN', $result[0]['cards'][2]['set'] );
		$this->assertNull( $result[0]['cards'][2]['nr'] );
	}

	public function test_deck_title_escapes_html() {
		$content = '[mtg_deck title="My Deck\'s & Yours <script>alert(\'xss\')</script>"]
3 Lightning Bolt
2 Mountain
Creatures
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

	public function test_deck_with_arena_format_generates_correct_urls() {
		$content = '[deck]
1 Airship Crash (FIN) 171
1 Mountain (fin)
2 Lightning Bolt
[/deck]';

		$output = do_shortcode( $content );

		// URL should include set and nr parameters
		$this->assertStringContainsString( 'href="https://deckbox.org/mtg/Airship Crash?set=FIN&amp;nr=171"', $output );
		// Set without number
		$this->assertStringContainsString( 'href="https://deckbox.org/mtg/Mountain?set=FIN"', $output );
		// Card without set/nr
		$this->assertStringContainsString( 'href="https://deckbox.org/mtg/Lightning Bolt"', $output );

		// Display text should show only card names (no set codes)
		$this->assertStringContainsString( '>Airship Crash</a>', $output );
		$this->assertStringContainsString( '>Mountain</a>', $output );
		$this->assertStringContainsString( '>Lightning Bolt</a>', $output );
	}

	public function test_card_shortcode_with_arena_format() {
		$output1 = do_shortcode( '[card]Lightning Bolt (M10) 146[/card]' );
		$this->assertStringContainsString( 'href="https://deckbox.org/mtg/Lightning Bolt?set=M10&amp;nr=146"', $output1 );
		$this->assertStringContainsString( '>Lightning Bolt</a>', $output1 );

		$output2 = do_shortcode( '[card]Mountain (FIN)[/card]' );
		$this->assertStringContainsString( 'href="https://deckbox.org/mtg/Mountain?set=FIN"', $output2 );
		$this->assertStringContainsString( '>Mountain</a>', $output2 );

		$output3 = do_shortcode( '[card]Tarmogoyf[/card]' );
		$this->assertStringContainsString( 'href="https://deckbox.org/mtg/Tarmogoyf"', $output3 );
		$this->assertStringContainsString( '>Tarmogoyf</a>', $output3 );
	}
}
