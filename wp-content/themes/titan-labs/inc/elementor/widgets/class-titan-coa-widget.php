<?php
/**
 * Certificate of Analysis table widget.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Searchable batch-results table built from per-product lab metadata.
 */
class Titan_Coa_Widget extends Titan_Widget_Base {

	public function get_name() {
		return 'titan-coa';
	}

	public function get_title() {
		return __( 'COA Table', 'titan-labs' );
	}

	public function get_icon() {
		return 'eicon-table';
	}

	protected function register_controls() {

		$this->start_controls_section(
			'content',
			array( 'label' => __( 'Content', 'titan-labs' ) )
		);

		$this->add_heading_controls(
			__( 'Certificate of Analysis', 'titan-labs' ),
			__( 'Batch Testing Results', 'titan-labs' ),
			__( 'Every batch is independently tested before release. Search any peptide or batch number, open its Certificate of Analysis, and verify it independently with the testing lab.', 'titan-labs' )
		);

		$this->add_control(
			'anchor',
			array(
				'label'       => __( 'Anchor id', 'titan-labs' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'coa',
				'description' => __( 'Lets you link to this table with #coa.', 'titan-labs' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'display',
			array( 'label' => __( 'Table', 'titan-labs' ) )
		);

		$this->add_control(
			'show_search',
			array(
				'label'        => __( 'Show search & filter', 'titan-labs' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'   => __( 'Maximum rows', 'titan-labs' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 5,
				'max'     => 300,
				'default' => 60,
			)
		);

		$this->add_control(
			'columns_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => '<div style="font-size:12px;line-height:1.5">'
					. esc_html__( 'Batch data comes from each product’s "Titan Labs — Lab Data" box.', 'titan-labs' )
					. '</div>',
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->add_background_control();

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		if ( ! function_exists( 'titan_get_coa_rows' ) ) {
			return;
		}

		$rows = titan_get_coa_rows( (int) ( $s['limit'] ?? 60 ) );

		if ( ! $rows ) {
			$this->render_placeholder( __( 'No batch data yet — add a Batch # to a product’s Lab Data box to populate this table.', 'titan-labs' ) );
			return;
		}

		$names = array_values( array_unique( wp_list_pluck( $rows, 'name' ) ) );
		sort( $names );

		$this->open_section( $s, '', $s['anchor'] ?? 'coa' );
		$this->render_heading( $s );

		if ( 'yes' === ( $s['show_search'] ?? 'yes' ) ) :
			?>
			<div class="tl-coa__controls">
				<input type="search"
					data-coa-search
					placeholder="<?php esc_attr_e( 'Search peptide or batch number…', 'titan-labs' ); ?>"
					aria-label="<?php esc_attr_e( 'Search Certificates of Analysis', 'titan-labs' ); ?>">

				<select data-coa-filter aria-label="<?php esc_attr_e( 'Filter by peptide', 'titan-labs' ); ?>">
					<option value=""><?php esc_html_e( 'All peptides', 'titan-labs' ); ?></option>
					<?php foreach ( $names as $name ) : ?>
						<option value="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php
		endif;
		?>

		<div class="tl-tablewrap">
			<table class="tl-table" data-coa-table>
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Product', 'titan-labs' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Size', 'titan-labs' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Batch #', 'titan-labs' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Tested', 'titan-labs' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Purity', 'titan-labs' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Net Content', 'titan-labs' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Heavy Metals', 'titan-labs' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Endotoxins', 'titan-labs' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Sterility', 'titan-labs' ); ?></th>
						<th scope="col"><?php esc_html_e( 'COA', 'titan-labs' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $rows as $row ) : ?>
						<tr data-name="<?php echo esc_attr( $row['name'] ); ?>"
							data-batch="<?php echo esc_attr( $row['batch'] ); ?>">
							<td><strong><?php echo esc_html( $row['name'] ); ?></strong></td>
							<td><?php echo esc_html( $row['size'] ); ?></td>
							<td><?php echo esc_html( $row['batch'] ); ?></td>
							<td><?php echo esc_html( $row['tested'] ); ?></td>
							<td><?php echo esc_html( $row['purity'] ); ?></td>
							<td><?php echo esc_html( $row['net'] ); ?></td>
							<td class="<?php echo 'Pass' === $row['heavy_metals'] ? 'tl-pass' : ''; ?>"><?php echo esc_html( $row['heavy_metals'] ); ?></td>
							<td class="<?php echo 'Pass' === $row['endotoxins'] ? 'tl-pass' : ''; ?>"><?php echo esc_html( $row['endotoxins'] ); ?></td>
							<td class="<?php echo 'Pass' === $row['sterility'] ? 'tl-pass' : ''; ?>"><?php echo esc_html( $row['sterility'] ); ?></td>
							<td>
								<?php if ( $row['coa'] ) : ?>
									<a href="<?php echo esc_url( $row['coa'] ); ?>" target="_blank" rel="noopener">
										<?php esc_html_e( 'View COA', 'titan-labs' ); ?>
									</a>
								<?php else : ?>
									<span class="tl-muted">—</span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					<tr data-coa-empty hidden>
						<td colspan="10" class="tl-text-center tl-muted">
							<?php esc_html_e( 'No matching batches found.', 'titan-labs' ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<?php
		$this->close_section();
	}
}
