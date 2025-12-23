<?php
/**
 * Class DeckIntegrationTest
 *
 * @package Magic_The_Gathering_Card_Tooltips
 */

/**
 * Integration tests for deck shortcode rendering.
 */
class DeckIntegrationTest extends WP_UnitTestCase {

	private $plugin;

	public function setUp(): void {
		parent::setUp();
		$this->plugin = new Deckbox_Tooltip_plugin();
	}

	/**
	 * Test shortcodes generate proper deckbox links and structure.
	 */
	public function test_shortcodes_generate_proper_html() {
		// Test card shortcode
		$card_output = do_shortcode( '[card]Lightning Bolt[/card]' );
		$this->assertStringContainsString( 'class="deckbox_link"', $card_output );
		$this->assertStringContainsString( 'href="https://deckbox.org/mtg/Lightning Bolt"', $card_output );

		// Test deck shortcode with title
		$deck_output = do_shortcode( '[deck title="My Deck"]
Creatures
4 Tarmogoyf
[/deck]' );

		$this->assertStringContainsString( '<h3 class="mtg_deck_title">My Deck</h3>', $deck_output );
		$this->assertStringContainsString( 'class="mtg_deck', $deck_output );
		$this->assertStringContainsString( 'Creatures (4)', $deck_output );
		$this->assertStringContainsString( 'href="https://deckbox.org/mtg/Tarmogoyf"', $deck_output );

		// Test short aliases
		$this->assertStringContainsString( 'deckbox_link', do_shortcode( '[c]Black Lotus[/c]' ) );
		$this->assertStringContainsString( 'mtg_deck', do_shortcode( '[d]4 Sol Ring[/d]' ) );
	}

	/**
	 * Test deck with Sideboard and Lands triggers column breaks.
	 */
	public function test_deck_column_breaks() {
		$content = '[deck]
Creatures
4 Tarmogoyf

Lands
4 Forest

Sideboard
3 Pyroblast
[/deck]';

		$output = do_shortcode( $content );

		// Check for column breaks before Lands and Sideboard
		$this->assertStringContainsString( '</td><td>', $output );
		$this->assertStringContainsString( 'Lands (4)', $output );
		$this->assertStringContainsString( 'Sideboard (3)', $output );
	}

	/**
	 * Test WordPress post with deck shortcode (end-to-end).
	 */
	public function test_post_with_deck_shortcode() {
		$post_id = $this->factory->post->create( array(
			'post_title'   => 'My Deck List',
			'post_content' => '[deck]4 Urza\'s Saga[/deck]',
			'post_status'  => 'publish',
		) );

		$post = get_post( $post_id );
		$output = apply_filters( 'the_content', $post->post_content );

		// Verify shortcode was processed
		$this->assertStringNotContainsString( '[deck]', $output );
		$this->assertStringContainsString( 'class="mtg_deck', $output );
		// WordPress converts apostrophes to right single quotation marks
		$this->assertStringContainsString( 'Urza&#8217;s Saga', $output );
	}

	/**
	 * Test embedded style shows card image.
	 */
	public function test_embedded_style() {
		$content = '[deck style="embedded"]4 Lightning Bolt[/deck]';
		$output = do_shortcode( $content );

		$this->assertStringContainsString( 'class="card_box"', $output );
		$this->assertStringContainsString( 'src="https://deckbox.org/mtg/Lightning Bolt/tooltip"', $output );
	}
}
