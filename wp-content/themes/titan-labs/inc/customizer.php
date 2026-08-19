<?php
/**
 * Customizer settings for Titan Labs.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers all theme panels, sections and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function titan_customize_register( $wp_customize ) {

	$wp_customize->add_panel( 'titan_panel', array(
		'title'    => __( 'Titan Labs Settings', 'titan-labs' ),
		'priority' => 20,
	) );

	/* ---------------------------------------------------------------
	 * Announcement bar
	 * ------------------------------------------------------------ */
	$wp_customize->add_section( 'titan_announce', array(
		'title' => __( 'Announcement Bar', 'titan-labs' ),
		'panel' => 'titan_panel',
	) );

	$wp_customize->add_setting( 'titan_announce_text', array(
		'default'           => __( 'Third-party tested to ≥99% purity · Tracked DHL shipping across Europe', 'titan-labs' ),
		'sanitize_callback' => 'wp_kses_post',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'titan_announce_text', array(
		'label'   => __( 'Announcement text', 'titan-labs' ),
		'section' => 'titan_announce',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'titan_announce_enabled', array(
		'default'           => true,
		'sanitize_callback' => 'titan_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'titan_announce_enabled', array(
		'label'   => __( 'Show announcement bar', 'titan-labs' ),
		'section' => 'titan_announce',
		'type'    => 'checkbox',
	) );

	/* ---------------------------------------------------------------
	 * Hero
	 * ------------------------------------------------------------ */
	$wp_customize->add_section( 'titan_hero', array(
		'title' => __( 'Homepage Hero', 'titan-labs' ),
		'panel' => 'titan_panel',
	) );

	$hero_fields = array(
		'titan_hero_eyebrow' => array(
			'label'   => __( 'Eyebrow', 'titan-labs' ),
			'default' => __( 'Research peptides · Europe', 'titan-labs' ),
			'type'    => 'text',
		),
		'titan_hero_title'   => array(
			'label'   => __( 'Headline', 'titan-labs' ),
			'default' => __( 'Lab-verified research peptides for Europe.', 'titan-labs' ),
			'type'    => 'text',
		),
		'titan_hero_sub'     => array(
			'label'   => __( 'Sub-headline', 'titan-labs' ),
			'default' => __( 'Buy research-grade peptides built for the lab. Every Titan Labs peptide is third-party HPLC and mass-spec tested to ≥99% purity, with a Certificate of Analysis on every peptide — discreetly & securely delivered across Europe.', 'titan-labs' ),
			'type'    => 'textarea',
		),
		'titan_hero_cta_text' => array(
			'label'   => __( 'Primary button text', 'titan-labs' ),
			'default' => __( 'Shop Research Peptides', 'titan-labs' ),
			'type'    => 'text',
		),
		'titan_hero_cta_url'  => array(
			'label'   => __( 'Primary button URL', 'titan-labs' ),
			'default' => '/shop/',
			'type'    => 'url',
		),
	);

	foreach ( $hero_fields as $id => $args ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $args['default'],
			'sanitize_callback' => 'url' === $args['type'] ? 'esc_url_raw' : 'wp_kses_post',
			'transport'         => 'refresh',
		) );
		$wp_customize->add_control( $id, array(
			'label'   => $args['label'],
			'section' => 'titan_hero',
			'type'    => 'url' === $args['type'] ? 'url' : $args['type'],
		) );
	}

	$wp_customize->add_setting( 'titan_hero_image', array( 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control(
		new WP_Customize_Media_Control( $wp_customize, 'titan_hero_image', array(
			'label'     => __( 'Hero image', 'titan-labs' ),
			'section'   => 'titan_hero',
			'mime_type' => 'image',
		) )
	);

	/* ---------------------------------------------------------------
	 * Trust stats
	 * ------------------------------------------------------------ */
	$wp_customize->add_section( 'titan_trust', array(
		'title' => __( 'Trust Stats', 'titan-labs' ),
		'panel' => 'titan_panel',
	) );

	$trust_defaults = array(
		1 => array( '≥99%', __( 'Tested purity', 'titan-labs' ) ),
		2 => array( '100%', __( '3rd-party verified', 'titan-labs' ) ),
		3 => array( __( 'Europe', 'titan-labs' ), __( 'Tracked DHL shipping', 'titan-labs' ) ),
	);

	foreach ( $trust_defaults as $i => $pair ) {
		$wp_customize->add_setting( "titan_trust_{$i}_value", array(
			'default'           => $pair[0],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( "titan_trust_{$i}_value", array(
			/* translators: %d: stat number */
			'label'   => sprintf( __( 'Stat %d — value', 'titan-labs' ), $i ),
			'section' => 'titan_trust',
			'type'    => 'text',
		) );

		$wp_customize->add_setting( "titan_trust_{$i}_label", array(
			'default'           => $pair[1],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( "titan_trust_{$i}_label", array(
			/* translators: %d: stat number */
			'label'   => sprintf( __( 'Stat %d — label', 'titan-labs' ), $i ),
			'section' => 'titan_trust',
			'type'    => 'text',
		) );
	}

	/* ---------------------------------------------------------------
	 * Stack builder
	 * ------------------------------------------------------------ */
	$wp_customize->add_section( 'titan_stack', array(
		'title'       => __( 'Stack Builder Discount', 'titan-labs' ),
		'panel'       => 'titan_panel',
		'description' => __( 'Automatic volume discount applied at cart level.', 'titan-labs' ),
	) );

	$wp_customize->add_setting( 'titan_stack_enabled', array(
		'default'           => true,
		'sanitize_callback' => 'titan_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'titan_stack_enabled', array(
		'label'   => __( 'Enable stack discount', 'titan-labs' ),
		'section' => 'titan_stack',
		'type'    => 'checkbox',
	) );

	$stack_numbers = array(
		'titan_stack_tier1_qty' => array( __( 'Tier 1 — minimum items', 'titan-labs' ), 4 ),
		'titan_stack_tier1_pct' => array( __( 'Tier 1 — discount %', 'titan-labs' ), 5 ),
		'titan_stack_tier2_qty' => array( __( 'Tier 2 — minimum items', 'titan-labs' ), 10 ),
		'titan_stack_tier2_pct' => array( __( 'Tier 2 — discount %', 'titan-labs' ), 10 ),
	);

	foreach ( $stack_numbers as $id => $args ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $args[1],
			'sanitize_callback' => 'absint',
		) );
		$wp_customize->add_control( $id, array(
			'label'       => $args[0],
			'section'     => 'titan_stack',
			'type'        => 'number',
			'input_attrs' => array( 'min' => 0, 'max' => 100 ),
		) );
	}

	/* ---------------------------------------------------------------
	 * Age gate
	 * ------------------------------------------------------------ */
	$wp_customize->add_section( 'titan_gate', array(
		'title' => __( 'Age Verification', 'titan-labs' ),
		'panel' => 'titan_panel',
	) );

	$wp_customize->add_setting( 'titan_age_gate', array(
		'default'           => true,
		'sanitize_callback' => 'titan_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'titan_age_gate', array(
		'label'   => __( 'Enable age verification gate', 'titan-labs' ),
		'section' => 'titan_gate',
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'titan_min_age', array(
		'default'           => 21,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'titan_min_age', array(
		'label'       => __( 'Minimum age', 'titan-labs' ),
		'section'     => 'titan_gate',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 16, 'max' => 25 ),
	) );

	/* ---------------------------------------------------------------
	 * Footer
	 * ------------------------------------------------------------ */
	$wp_customize->add_section( 'titan_footer', array(
		'title' => __( 'Footer', 'titan-labs' ),
		'panel' => 'titan_panel',
	) );

	$wp_customize->add_setting( 'titan_footer_about', array(
		'default'           => __( 'Lab-verified research peptides for Europe. Purity measured, identity confirmed, and a Certificate of Analysis published on every peptide — discreetly & securely delivered across Europe.', 'titan-labs' ),
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'titan_footer_about', array(
		'label'   => __( 'About text', 'titan-labs' ),
		'section' => 'titan_footer',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'titan_footer_disclaimer', array(
		'default'           => __( 'Products from this website are NOT intended to be used to diagnose or treat any medical condition or disease. Products on this website are sold for laboratory research purposes only and are for in-vitro lab research use only. The products are not medicines or drugs and have not been approved to prevent, treat, diagnose, mitigate, or cure any disease, ailment or medical condition.', 'titan-labs' ),
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'titan_footer_disclaimer', array(
		'label'   => __( 'Legal disclaimer', 'titan-labs' ),
		'section' => 'titan_footer',
		'type'    => 'textarea',
	) );
}
add_action( 'customize_register', 'titan_customize_register' );

/**
 * Checkbox sanitizer.
 *
 * @param mixed $checked Raw value.
 * @return bool
 */
function titan_sanitize_checkbox( $checked ) {
	return ( isset( $checked ) && true === (bool) $checked );
}
