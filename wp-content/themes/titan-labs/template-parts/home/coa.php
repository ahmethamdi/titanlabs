<?php
/**
 * Homepage — Certificate of Analysis batch table.
 *
 * @package TitanLabs
 */

defined( 'ABSPATH' ) || exit;

$rows = titan_get_coa_rows( 60 );

if ( ! $rows ) {
	return;
}

$names = array_values( array_unique( wp_list_pluck( $rows, 'name' ) ) );
sort( $names );
?>

<section class="tl-section" id="coa" style="scroll-margin-top:6rem">
	<div class="tl-container">

		<div class="tl-sectionhead">
			<div>
				<p class="tl-eyebrow"><?php esc_html_e( 'Certificate of Analysis', 'titan-labs' ); ?></p>
				<h2><?php esc_html_e( 'Batch Testing Results', 'titan-labs' ); ?></h2>
				<p class="tl-lede tl-mb-0">
					<?php esc_html_e( 'Every batch is independently tested before release. Search any peptide or batch number, open its Certificate of Analysis, and verify it independently with the testing lab.', 'titan-labs' ); ?>
				</p>
			</div>
		</div>

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

	</div>
</section>
