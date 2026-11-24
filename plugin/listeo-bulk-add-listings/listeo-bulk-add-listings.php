<?php
/**
 * Plugin Name: Listeo Bulk Add Listings
 * Plugin URI:  https://purethemes.net/
 * Description: Bulk create pending Listeo listings by uploading a CSV file, with full syntax and taxonomy validation, example CSV download, and author assignment.
 * Version:     1.0.0
 * Author:      Custom
 * Text Domain: listeo-bulk-add-listings
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main Class for Listeo Bulk Add Listings
 */
class Listeo_Bulk_Add_Listings {

	/**
	 * Singleton instance
	 *
	 * @var Listeo_Bulk_Add_Listings|null
	 */
	private static $instance = null;

	/**
	 * Menu slug for the admin page
	 *
	 * @var string
	 */
	const MENU_SLUG = 'listeo-bulk-add-listings';

	/**
	 * Transient key prefix for storing upload results/errors per user
	 *
	 * @var string
	 */
	const TRANSIENT_PREFIX = 'listeo_bulk_add_listings_result_';

	/**
	 * Canonical CSV columns definition.
	 * Maps normalized (lowercase) header names to internal keys and labels.
	 *
	 * @return array
	 */
	public static function get_csv_columns() {
		return array(
			'listing name'     => array(
				'label'       => 'Listing name',
				'key'         => 'listing_name',
				'required'    => true,
				'description' => __( 'The main title of the listing (maps to Post Title / listing_title).', 'listeo-bulk-add-listings' ),
			),
			'listing type'     => array(
				'label'       => 'Listing type',
				'key'         => '_listing_type (and listing_type)',
				'required'    => true,
				'description' => __( 'Listing type slug or name, e.g. service, rental, event, classifieds (maps to _listing_type and listing_type).', 'listeo-bulk-add-listings' ),
			),
			'category'         => array(
				'label'       => 'category',
				'key'         => 'category',
				'required'    => true,
				'description' => __( 'Existing category name, slug, or ID in Listing Categories (listing_category). Separate multiple categories with commas.', 'listeo-bulk-add-listings' ),
			),
			'keywords'         => array(
				'label'       => 'Keywords',
				'key'         => 'keywords',
				'required'    => false,
				'description' => __( 'Comma-separated search keywords (maps to keywords).', 'listeo-bulk-add-listings' ),
			),
			'description'      => array(
				'label'       => 'Description',
				'key'         => 'description',
				'required'    => false,
				'description' => __( 'Full listing description (maps to Post Content / listing_description).', 'listeo-bulk-add-listings' ),
			),
			'video embed'      => array(
				'label'       => 'Video embed',
				'key'         => '_video',
				'required'    => false,
				'description' => __( 'Video URL or embed (maps to _video).', 'listeo-bulk-add-listings' ),
			),
			'phone'            => array(
				'label'       => 'Phone',
				'key'         => '_phone',
				'required'    => false,
				'description' => __( 'Contact phone number (maps to _phone).', 'listeo-bulk-add-listings' ),
			),
			'website'          => array(
				'label'       => 'Website',
				'key'         => '_website',
				'required'    => false,
				'description' => __( 'Website URL (maps to _website).', 'listeo-bulk-add-listings' ),
			),
			'email'            => array(
				'label'       => 'Email',
				'key'         => '_email',
				'required'    => false,
				'description' => __( 'Contact email address (maps to _email). Validated for email syntax if provided.', 'listeo-bulk-add-listings' ),
			),
			'facebook'         => array(
				'label'       => 'FaceBook',
				'key'         => '_facebook',
				'required'    => false,
				'description' => __( 'Facebook profile/page URL (maps to _facebook).', 'listeo-bulk-add-listings' ),
			),
			'youtube'          => array(
				'label'       => 'Youtube',
				'key'         => '_youtube',
				'required'    => false,
				'description' => __( 'YouTube channel/video URL (maps to _youtube).', 'listeo-bulk-add-listings' ),
			),
			'instagram'        => array(
				'label'       => 'instagram',
				'key'         => '_instagram',
				'required'    => false,
				'description' => __( 'Instagram profile URL or handle (maps to _instagram).', 'listeo-bulk-add-listings' ),
			),
			'address'          => array(
				'label'       => 'Address',
				'key'         => '_address',
				'required'    => false,
				'description' => __( 'Street / human-readable address (maps to _address and _friendly_address).', 'listeo-bulk-add-listings' ),
			),
			'region'           => array(
				'label'       => 'Region',
				'key'         => 'region',
				'required'    => false,
				'description' => __( 'Existing region name, slug, or ID in Regions (region taxonomy). Separate multiple regions with commas.', 'listeo-bulk-add-listings' ),
			),
			'longitude'        => array(
				'label'       => 'Longitude',
				'key'         => '_geolocation_long',
				'required'    => false,
				'description' => __( 'Longitude coordinate between -180 and 180 (maps to _geolocation_long).', 'listeo-bulk-add-listings' ),
			),
			'latitude'         => array(
				'label'       => 'latitude',
				'key'         => '_geolocation_lat',
				'required'    => false,
				'description' => __( 'Latitude coordinate between -90 and 90 (maps to _geolocation_lat).', 'listeo-bulk-add-listings' ),
			),
			'listing timezone' => array(
				'label'       => 'Listing Timezone',
				'key'         => '_listing_timezone',
				'required'    => false,
				'description' => __( 'Timezone identifier such as America/New_York or UTC-5 (maps to _listing_timezone).', 'listeo-bulk-add-listings' ),
			),
			'policy'           => array(
				'label'       => 'Policy',
				'key'         => '_service_cancellation_refund_policy',
				'required'    => false,
				'description' => __( 'Cancellation / refund policy text (maps to _service_cancellation_refund_policy).', 'listeo-bulk-add-listings' ),
			),
		);
	}

	/**
	 * Get singleton instance
	 *
	 * @return Listeo_Bulk_Add_Listings
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 25 );
		add_action( 'admin_init', array( $this, 'handle_download_template' ) );
		add_action( 'admin_init', array( $this, 'handle_csv_upload' ) );
	}

	/**
	 * Register "Bulk Add Listings" submenu page under Listings (edit.php?post_type=listing)
	 */
	public function register_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=listing',
			__( 'Bulk Add Listings', 'listeo-bulk-add-listings' ),
			__( 'Bulk Add Listings', 'listeo-bulk-add-listings' ),
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Handle downloading the example CSV template
	 */
	public function handle_download_template() {
		if ( ! isset( $_GET['page'] ) || self::MENU_SLUG !== $_GET['page'] ) {
			return;
		}

		if ( ! isset( $_GET['action'] ) || 'download_template' !== $_GET['action'] ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to download this template.', 'listeo-bulk-add-listings' ) );
		}

		check_admin_referer( 'listeo_bulk_download_template', 'template_nonce' );

		$columns = self::get_csv_columns();
		$headers = array();
		foreach ( $columns as $col ) {
			$headers[] = $col['label'];
		}

		// Use an existing category and region for the sample row if available on the site.
		$sample_category = 'Services';
		$existing_cats   = get_terms(
			array(
				'taxonomy'   => 'listing_category',
				'hide_empty' => false,
				'number'     => 1,
			)
		);
		if ( ! is_wp_error( $existing_cats ) && ! empty( $existing_cats ) ) {
			$sample_category = $existing_cats[0]->name;
		}

		$sample_region    = 'New York';
		$existing_regions = get_terms(
			array(
				'taxonomy'   => 'region',
				'hide_empty' => false,
				'number'     => 1,
			)
		);
		if ( ! is_wp_error( $existing_regions ) && ! empty( $existing_regions ) ) {
			$sample_region = $existing_regions[0]->name;
		}

		$sample_timezone = wp_timezone_string();
		if ( empty( $sample_timezone ) ) {
			$sample_timezone = 'America/New_York';
		}

		$valid_types      = $this->get_valid_listing_types();
		$sample_type_slug = ! empty( $valid_types ) ? key( $valid_types ) : 'service';

		$sample_row = array(
			'Downtown Wellness Studio',
			$sample_type_slug,
			$sample_category,
			'wellness, massage, spa, relaxation',
			'Full-service wellness studio offering therapeutic massage and holistic treatments.',
			'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
			'+1 (555) 234-5678',
			'https://example.com',
			'contact@example.com',
			'https://facebook.com/example',
			'https://youtube.com/@example',
			'https://instagram.com/example',
			'123 Main Street, Suite 100, New York, NY 10001',
			$sample_region,
			'-74.0060',
			'40.7128',
			$sample_timezone,
			'Full refund for cancellations made at least 24 hours in advance.',
		);

		if ( ob_get_length() ) {
			ob_end_clean();
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="listeo-bulk-listings-template.csv"' );

		$output = fopen( 'php://output', 'w' );
		// Add UTF-8 BOM for Excel compatibility.
		fwrite( $output, "\xEF\xBB\xBF" );
		fputcsv( $output, $headers );
		fputcsv( $output, $sample_row );
		fclose( $output );
		exit;
	}

	/**
	 * Handle CSV file upload, syntax validation, and bulk listing creation
	 */
	public function handle_csv_upload() {
		if ( ! isset( $_POST['listeo_bulk_add_submit'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'listeo-bulk-add-listings' ) );
		}

		check_admin_referer( 'listeo_bulk_add_listings_upload', 'listeo_bulk_add_nonce' );

		$errors       = array();
		$author_id    = isset( $_POST['listing_author'] ) ? absint( $_POST['listing_author'] ) : 0;
		$author_user  = $author_id ? get_userdata( $author_id ) : false;

		if ( ! $author_user ) {
			$errors[] = array(
				'row'     => '-',
				'listing' => '-',
				'column'  => __( 'Author', 'listeo-bulk-add-listings' ),
				'message' => __( 'Please select a valid WordPress user as the author for the uploaded listings.', 'listeo-bulk-add-listings' ),
			);
		}

		if ( ! taxonomy_exists( 'listing_category' ) ) {
			$errors[] = array(
				'row'     => '-',
				'listing' => '-',
				'column'  => 'category',
				'message' => __( 'The "listing_category" taxonomy is not registered. Please ensure Listeo Core is active.', 'listeo-bulk-add-listings' ),
			);
		}

		// Check uploaded file.
		if ( empty( $_FILES['csv_file'] ) || ! isset( $_FILES['csv_file']['error'] ) || UPLOAD_ERR_NO_FILE === $_FILES['csv_file']['error'] ) {
			$errors[] = array(
				'row'     => '-',
				'listing' => '-',
				'column'  => __( 'CSV File', 'listeo-bulk-add-listings' ),
				'message' => __( 'Please select a CSV file to upload.', 'listeo-bulk-add-listings' ),
			);
		} elseif ( UPLOAD_ERR_OK !== $_FILES['csv_file']['error'] ) {
			$errors[] = array(
				'row'     => '-',
				'listing' => '-',
				'column'  => __( 'CSV File', 'listeo-bulk-add-listings' ),
				'message' => sprintf(
					/* translators: %d: PHP file upload error code */
					__( 'File upload failed with error code %d.', 'listeo-bulk-add-listings' ),
					intval( $_FILES['csv_file']['error'] )
				),
			);
		} else {
			$file_name = isset( $_FILES['csv_file']['name'] ) ? sanitize_file_name( wp_unslash( $_FILES['csv_file']['name'] ) ) : '';
			$file_ext  = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );

			if ( 'csv' !== $file_ext ) {
				$errors[] = array(
					'row'     => '-',
					'listing' => '-',
					'column'  => __( 'CSV File', 'listeo-bulk-add-listings' ),
					'message' => __( 'Invalid file extension. Please upload a valid .csv file.', 'listeo-bulk-add-listings' ),
				);
			}
		}

		if ( ! empty( $errors ) ) {
			$this->save_results_and_redirect(
				array(
					'status'    => 'error',
					'author_id' => $author_id,
					'errors'    => $errors,
					'created'   => array(),
				)
			);
		}

		$tmp_name = $_FILES['csv_file']['tmp_name'];
		$parsed   = $this->parse_and_validate_csv( $tmp_name );

		if ( ! empty( $parsed['errors'] ) ) {
			$this->save_results_and_redirect(
				array(
					'status'    => 'error',
					'author_id' => $author_id,
					'errors'    => $parsed['errors'],
					'created'   => array(),
				)
			);
		}

		// All rows passed syntax and data validation! Proceed to create pending listings.
		$created_listings = array();

		foreach ( $parsed['rows'] as $row_item ) {
			$result = $this->create_pending_listing( $row_item, $author_id );
			if ( is_wp_error( $result ) ) {
				$errors[] = array(
					'row'     => $row_item['row_number'],
					'listing' => $row_item['listing_name'],
					'column'  => __( 'Database Insert', 'listeo-bulk-add-listings' ),
					'message' => $result->get_error_message(),
				);
			} else {
				$created_listings[] = array(
					'id'       => $result,
					'title'    => $row_item['listing_name'],
					'row'      => $row_item['row_number'],
					'edit_url' => get_edit_post_link( $result, 'raw' ),
				);
			}
		}

		$this->save_results_and_redirect(
			array(
				'status'    => empty( $errors ) ? 'success' : 'partial_error',
				'author_id' => $author_id,
				'errors'    => $errors,
				'created'   => $created_listings,
			)
		);
	}

	/**
	 * Parse and validate the uploaded CSV file
	 *
	 * @param string $file_path Path to the uploaded temporary CSV file.
	 * @return array {
	 *     @type array $errors List of validation errors.
	 *     @type array $rows   List of validated, normalized row data ready for insertion.
	 * }
	 */
	private function parse_and_validate_csv( $file_path ) {
		$errors         = array();
		$validated_rows = array();

		// Enable auto-detection of line endings for Mac/Windows/Unix CSV files.
		$prev_auto_detect = ini_get( 'auto_detect_line_endings' );
		@ini_set( 'auto_detect_line_endings', '1' );

		$handle = fopen( $file_path, 'r' );
		if ( false === $handle ) {
			$errors[] = array(
				'row'     => '-',
				'listing' => '-',
				'column'  => __( 'CSV File', 'listeo-bulk-add-listings' ),
				'message' => __( 'Unable to open uploaded CSV file for reading.', 'listeo-bulk-add-listings' ),
			);
			return array(
				'errors' => $errors,
				'rows'   => array(),
			);
		}

		// Detect delimiter from the first line (supports comma, semicolon, or tab, defaulting to comma).
		$delimiter   = $this->detect_csv_delimiter( $handle );
		$raw_headers = fgetcsv( $handle, 0, $delimiter );

		if ( false === $raw_headers || empty( $raw_headers ) ) {
			fclose( $handle );
			@ini_set( 'auto_detect_line_endings', $prev_auto_detect );
			$errors[] = array(
				'row'     => 1,
				'listing' => '-',
				'column'  => __( 'Header Row', 'listeo-bulk-add-listings' ),
				'message' => __( 'The CSV file is empty or missing the header row.', 'listeo-bulk-add-listings' ),
			);
			return array(
				'errors' => $errors,
				'rows'   => array(),
			);
		}

		// Strip UTF-8 BOM from the first header cell if present and normalize headers.
		$raw_headers[0]  = preg_replace( '/^\xEF\xBB\xBF/', '', (string) $raw_headers[0] );
		$expected_cols   = self::get_csv_columns();
		$header_index    = array();
		$unknown_headers = array();

		foreach ( $raw_headers as $idx => $header_text ) {
			$normalized = strtolower( trim( preg_replace( '/\s+/', ' ', (string) $header_text ) ) );
			if ( '' === $normalized ) {
				continue;
			}
			// Ignore legacy "title" column if present in user's file.
			if ( 'title' === $normalized ) {
				$header_index['title'] = $idx;
				continue;
			}
			// Allow "listing_type" or "_listing_type" as aliases for "listing type".
			if ( 'listing_type' === $normalized || '_listing_type' === $normalized ) {
				$normalized = 'listing type';
			}

			if ( isset( $expected_cols[ $normalized ] ) ) {
				if ( isset( $header_index[ $normalized ] ) ) {
					$errors[] = array(
						'row'     => 1,
						'listing' => '-',
						'column'  => $header_text,
						'message' => sprintf(
							/* translators: %s: duplicate column header name */
							__( 'Duplicate column header "%s" found in CSV.', 'listeo-bulk-add-listings' ),
							$header_text
						),
					);
				} else {
					$header_index[ $normalized ] = $idx;
				}
			} else {
				$unknown_headers[] = $header_text;
			}
		}

		// Check for any missing expected columns.
		$missing_headers = array();
		foreach ( $expected_cols as $norm_name => $col_info ) {
			if ( ! isset( $header_index[ $norm_name ] ) ) {
				$missing_headers[] = $col_info['label'];
			}
		}

		if ( ! empty( $missing_headers ) ) {
			$errors[] = array(
				'row'     => 1,
				'listing' => '-',
				'column'  => __( 'Header Row', 'listeo-bulk-add-listings' ),
				'message' => sprintf(
					/* translators: %s: comma-separated list of missing column headers */
					__( 'Missing required CSV column header(s): %s.', 'listeo-bulk-add-listings' ),
					implode( ', ', $missing_headers )
				),
			);
		}

		if ( ! empty( $unknown_headers ) ) {
			$errors[] = array(
				'row'     => 1,
				'listing' => '-',
				'column'  => __( 'Header Row', 'listeo-bulk-add-listings' ),
				'message' => sprintf(
					/* translators: %s: comma-separated list of unexpected column headers */
					__( 'Unrecognized CSV column header(s): %s.', 'listeo-bulk-add-listings' ),
					implode( ', ', $unknown_headers )
				),
			);
		}

		// If header syntax has errors, stop early and report header errors.
		if ( ! empty( $errors ) ) {
			fclose( $handle );
			@ini_set( 'auto_detect_line_endings', $prev_auto_detect );
			return array(
				'errors' => $errors,
				'rows'   => array(),
			);
		}

		$expected_col_count  = count( $raw_headers );
		$row_number          = 1;
		$valid_timezones     = $this->get_valid_timezones_map();
		$valid_listing_types = $this->get_valid_listing_types();

		while ( ( $row = fgetcsv( $handle, 0, $delimiter ) ) !== false ) {
			$row_number++;

			// Skip completely empty rows (e.g. trailing blank line at EOF).
			if ( $this->is_row_empty( $row ) ) {
				continue;
			}

			// Extract listing name early for helpful error reporting.
			$listing_name_idx = $header_index['listing name'];
			$raw_listing_name = isset( $row[ $listing_name_idx ] ) ? trim( (string) $row[ $listing_name_idx ] ) : '';
			$display_name     = '' !== $raw_listing_name ? $raw_listing_name : __( '(Empty Listing Name)', 'listeo-bulk-add-listings' );

			// Check column count syntax on this row.
			if ( count( $row ) !== $expected_col_count ) {
				$errors[] = array(
					'row'     => $row_number,
					'listing' => $display_name,
					'column'  => __( 'Row Syntax', 'listeo-bulk-add-listings' ),
					'message' => sprintf(
						/* translators: 1: expected column count, 2: actual column count */
						__( 'Syntax error: Expected %1$d columns matching the header row, but found %2$d columns. Check for unescaped commas or quotes.', 'listeo-bulk-add-listings' ),
						$expected_col_count,
						count( $row )
					),
				);
				continue;
			}

			// Helper to get trimmed cell value by normalized header name.
			$get_val = function( $norm_header ) use ( $row, $header_index ) {
				if ( ! isset( $header_index[ $norm_header ] ) ) {
					return '';
				}
				$idx = $header_index[ $norm_header ];
				return isset( $row[ $idx ] ) ? trim( (string) $row[ $idx ] ) : '';
			};

			// 1. Validate Listing name (required).
			if ( '' === $raw_listing_name ) {
				$errors[] = array(
					'row'     => $row_number,
					'listing' => $display_name,
					'column'  => 'Listing name',
					'message' => __( 'Listing name is required and cannot be empty.', 'listeo-bulk-add-listings' ),
				);
			}

			// 2. Validate Listing type (required, must match a valid Listeo listing type).
			$raw_listing_type      = $get_val( 'listing type' );
			$resolved_listing_type = '';
			if ( '' === $raw_listing_type ) {
				$errors[] = array(
					'row'     => $row_number,
					'listing' => $display_name,
					'column'  => 'Listing type',
					'message' => __( 'Listing type is required and cannot be empty.', 'listeo-bulk-add-listings' ),
				);
			} else {
				$resolved_listing_type = $this->resolve_listing_type( $raw_listing_type, $valid_listing_types );
				if ( false === $resolved_listing_type ) {
					$errors[] = array(
						'row'     => $row_number,
						'listing' => $display_name,
						'column'  => 'Listing type',
						'message' => sprintf(
							/* translators: 1: invalid listing type value, 2: comma-separated list of valid listing types */
							__( 'Invalid Listing type "%1$s". Valid types are: %2$s.', 'listeo-bulk-add-listings' ),
							$raw_listing_type,
							implode( ', ', array_keys( $valid_listing_types ) )
						),
					);
				}
			}

			// 3. Validate category (required, must exist in listing_category taxonomy).
			$raw_category  = $get_val( 'category' );
			$category_ids  = array();
			if ( '' === $raw_category ) {
				$errors[] = array(
					'row'     => $row_number,
					'listing' => $display_name,
					'column'  => 'category',
					'message' => __( 'Category is required and cannot be empty.', 'listeo-bulk-add-listings' ),
				);
			} else {
				$cat_resolution = $this->resolve_taxonomy_terms( $raw_category, 'listing_category' );
				if ( ! empty( $cat_resolution['missing'] ) ) {
					$errors[] = array(
						'row'     => $row_number,
						'listing' => $display_name,
						'column'  => 'category',
						'message' => sprintf(
							/* translators: %s: non-existent category names */
							__( 'Category does not exist in Listing Categories: "%s".', 'listeo-bulk-add-listings' ),
							implode( '", "', $cat_resolution['missing'] )
						),
					);
				} else {
					$category_ids = $cat_resolution['term_ids'];
				}
			}

			// 3. Validate Region (if provided, must exist in region taxonomy).
			$raw_region = $get_val( 'region' );
			$region_ids = array();
			if ( '' !== $raw_region ) {
				if ( ! taxonomy_exists( 'region' ) ) {
					$errors[] = array(
						'row'     => $row_number,
						'listing' => $display_name,
						'column'  => 'Region',
						'message' => __( 'The "region" taxonomy is not registered on this site.', 'listeo-bulk-add-listings' ),
					);
				} else {
					$reg_resolution = $this->resolve_taxonomy_terms( $raw_region, 'region' );
					if ( ! empty( $reg_resolution['missing'] ) ) {
						$errors[] = array(
							'row'     => $row_number,
							'listing' => $display_name,
							'column'  => 'Region',
							'message' => sprintf(
								/* translators: %s: non-existent region names */
								__( 'Region does not exist in Regions: "%s".', 'listeo-bulk-add-listings' ),
								implode( '", "', $reg_resolution['missing'] )
							),
						);
					} else {
						$region_ids = $reg_resolution['term_ids'];
					}
				}
			}

			// 4. Validate Email (if provided).
			$raw_email = $get_val( 'email' );
			if ( '' !== $raw_email && ! is_email( $raw_email ) ) {
				$errors[] = array(
					'row'     => $row_number,
					'listing' => $display_name,
					'column'  => 'Email',
					'message' => sprintf(
						/* translators: %s: invalid email value */
						__( 'Invalid email address syntax: "%s".', 'listeo-bulk-add-listings' ),
						$raw_email
					),
				);
			}

			// 5. Validate Website URL (if provided).
			$raw_website = $get_val( 'website' );
			if ( '' !== $raw_website ) {
				$normalized_website = $this->normalize_url( $raw_website );
				if ( false === $normalized_website ) {
					$errors[] = array(
						'row'     => $row_number,
						'listing' => $display_name,
						'column'  => 'Website',
						'message' => sprintf(
							/* translators: %s: invalid website URL */
							__( 'Invalid Website URL syntax: "%s".', 'listeo-bulk-add-listings' ),
							$raw_website
						),
					);
				} else {
					$raw_website = $normalized_website;
				}
			}

			// 6. Validate Longitude (if provided: numeric between -180 and 180).
			$raw_longitude = $get_val( 'longitude' );
			if ( '' !== $raw_longitude ) {
				if ( ! is_numeric( $raw_longitude ) || floatval( $raw_longitude ) < -180 || floatval( $raw_longitude ) > 180 ) {
					$errors[] = array(
						'row'     => $row_number,
						'listing' => $display_name,
						'column'  => 'Longitude',
						'message' => sprintf(
							/* translators: %s: invalid longitude value */
							__( 'Invalid Longitude "%s". Must be a number between -180 and 180.', 'listeo-bulk-add-listings' ),
							$raw_longitude
						),
					);
				}
			}

			// 7. Validate latitude (if provided: numeric between -90 and 90).
			$raw_latitude = $get_val( 'latitude' );
			if ( '' !== $raw_latitude ) {
				if ( ! is_numeric( $raw_latitude ) || floatval( $raw_latitude ) < -90 || floatval( $raw_latitude ) > 90 ) {
					$errors[] = array(
						'row'     => $row_number,
						'listing' => $display_name,
						'column'  => 'latitude',
						'message' => sprintf(
							/* translators: %s: invalid latitude value */
							__( 'Invalid latitude "%s". Must be a number between -90 and 90.', 'listeo-bulk-add-listings' ),
							$raw_latitude
						),
					);
				}
			}

			// 8. Validate Listing Timezone (if provided).
			$raw_timezone        = $get_val( 'listing timezone' );
			$normalized_timezone = '';
			if ( '' !== $raw_timezone ) {
				$tz_Lookup = $this->validate_and_normalize_timezone( $raw_timezone, $valid_timezones );
				if ( false === $tz_Lookup ) {
					$errors[] = array(
						'row'     => $row_number,
						'listing' => $display_name,
						'column'  => 'Listing Timezone',
						'message' => sprintf(
							/* translators: %s: invalid timezone value */
							__( 'Invalid Listing Timezone "%s". Use a valid IANA timezone (e.g. America/New_York, Europe/London) or UTC offset (e.g. UTC-5, UTC+2).', 'listeo-bulk-add-listings' ),
							$raw_timezone
						),
					);
				} else {
					$normalized_timezone = $tz_Lookup;
				}
			}

			$validated_rows[] = array(
				'row_number'   => $row_number,
				'listing_name' => $raw_listing_name,
				'listing_type' => $resolved_listing_type,
				'category_ids' => $category_ids,
				'region_ids'   => $region_ids,
				'keywords'     => $get_val( 'keywords' ),
				'description'  => $get_val( 'description' ),
				'video'        => $get_val( 'video embed' ),
				'phone'        => $get_val( 'phone' ),
				'website'      => $raw_website,
				'email'        => $raw_email,
				'facebook'     => $get_val( 'facebook' ),
				'youtube'      => $get_val( 'youtube' ),
				'instagram'    => $get_val( 'instagram' ),
				'address'      => $get_val( 'address' ),
				'longitude'    => $raw_longitude,
				'latitude'     => $raw_latitude,
				'timezone'     => $normalized_timezone,
				'policy'       => $get_val( 'policy' ),
			);
		}

		fclose( $handle );
		@ini_set( 'auto_detect_line_endings', $prev_auto_detect );

		if ( empty( $validated_rows ) && empty( $errors ) ) {
			$errors[] = array(
				'row'     => '-',
				'listing' => '-',
				'column'  => __( 'CSV Data', 'listeo-bulk-add-listings' ),
				'message' => __( 'The uploaded CSV file contains header columns but no listing rows.', 'listeo-bulk-add-listings' ),
			);
		}

		return array(
			'errors' => $errors,
			'rows'   => $validated_rows,
		);
	}

	/**
	 * Insert a validated row as a pending Listeo listing
	 *
	 * @param array $row_data  Validated row associative array.
	 * @param int   $author_id WordPress User ID to assign as post_author.
	 * @return int|WP_Error    Created post ID or WP_Error on failure.
	 */
	private function create_pending_listing( $row_data, $author_id ) {
		$post_title   = sanitize_text_field( $row_data['listing_name'] );
		$post_content = wp_kses_post( $row_data['description'] );

		$post_args = array(
			'post_title'     => $post_title,
			'post_name'      => sanitize_title( $post_title ),
			'post_content'   => $post_content,
			'post_status'    => 'pending',
			'post_type'      => 'listing',
			'post_author'    => $author_id,
			'comment_status' => 'open',
		);

		$post_id = wp_insert_post( $post_args, true );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// Set Listeo Core listing type and standard fields.
		$listing_type = ! empty( $row_data['listing_type'] ) ? sanitize_key( $row_data['listing_type'] ) : 'service';
		update_post_meta( $post_id, '_listing_type', $listing_type );
		update_post_meta( $post_id, 'listing_type', $listing_type );
		update_post_meta( $post_id, 'listing_title', $post_title );
		update_post_meta( $post_id, 'listing_description', $post_content );

		// Set default Listeo Core metadata if not already initialized by save_post hooks.
		add_post_meta( $post_id, '_featured', '0', true );
		add_post_meta( $post_id, 'listeo-avg-rating', '0', true );
		add_post_meta( $post_id, '_listing_views_count', '0', true );

		// Assign Listing Categories (listing_category taxonomy).
		if ( ! empty( $row_data['category_ids'] ) ) {
			wp_set_object_terms( $post_id, array_map( 'intval', $row_data['category_ids'] ), 'listing_category', false );
		}

		// Assign Regions (region taxonomy).
		if ( ! empty( $row_data['region_ids'] ) ) {
			wp_set_object_terms( $post_id, array_map( 'intval', $row_data['region_ids'] ), 'region', false );
		}

		// Save mapped meta fields.
		update_post_meta( $post_id, 'keywords', sanitize_text_field( $row_data['keywords'] ) );
		update_post_meta( $post_id, '_video', sanitize_textarea_field( $row_data['video'] ) );
		update_post_meta( $post_id, '_phone', sanitize_text_field( $row_data['phone'] ) );
		update_post_meta( $post_id, '_website', esc_url_raw( $row_data['website'] ) );
		update_post_meta( $post_id, '_email', sanitize_email( $row_data['email'] ) );
		update_post_meta( $post_id, '_facebook', sanitize_text_field( $row_data['facebook'] ) );
		update_post_meta( $post_id, '_youtube', sanitize_text_field( $row_data['youtube'] ) );
		update_post_meta( $post_id, '_instagram', sanitize_text_field( $row_data['instagram'] ) );

		// Save Address to both _address and _friendly_address so Listeo displays and searches it in all configurations.
		$address = sanitize_text_field( $row_data['address'] );
		update_post_meta( $post_id, '_address', $address );
		update_post_meta( $post_id, '_friendly_address', $address );

		update_post_meta( $post_id, '_geolocation_long', sanitize_text_field( $row_data['longitude'] ) );
		update_post_meta( $post_id, '_geolocation_lat', sanitize_text_field( $row_data['latitude'] ) );
		update_post_meta( $post_id, '_listing_timezone', sanitize_text_field( $row_data['timezone'] ) );

		// Save custom Policy field (_service_cancellation_refund_policy).
		update_post_meta( $post_id, '_service_cancellation_refund_policy', wp_kses_post( $row_data['policy'] ) );

		return $post_id;
	}

	/**
	 * Resolve one or more taxonomy terms (by name, slug, or ID) against an existing taxonomy
	 *
	 * @param string $raw_input Raw cell string from CSV.
	 * @param string $taxonomy  Taxonomy name ('listing_category' or 'region').
	 * @return array {
	 *     @type array $term_ids Resolved integer term IDs.
	 *     @type array $missing  Term strings that could not be found.
	 * }
	 */
	private function resolve_taxonomy_terms( $raw_input, $taxonomy ) {
		$raw_input = trim( (string) $raw_input );
		if ( '' === $raw_input ) {
			return array(
				'term_ids' => array(),
				'missing'  => array(),
			);
		}

		// First check if the entire string matches a single term (handles term names that contain a comma).
		$single_match = $this->find_single_term_id( $raw_input, $taxonomy );
		if ( $single_match ) {
			return array(
				'term_ids' => array( $single_match ),
				'missing'  => array(),
			);
		}

		// Otherwise split by comma, semicolon, or pipe for multiple terms.
		$parts    = preg_split( '/[,;|]+/', $raw_input );
		$term_ids = array();
		$missing  = array();

		foreach ( $parts as $part ) {
			$candidate = trim( $part );
			if ( '' === $candidate ) {
				continue;
			}

			$term_id = $this->find_single_term_id( $candidate, $taxonomy );
			if ( $term_id ) {
				$term_ids[] = $term_id;
			} else {
				$missing[] = $candidate;
			}
		}

		return array(
			'term_ids' => array_values( array_unique( $term_ids ) ),
			'missing'  => $missing,
		);
	}

	/**
	 * Find a single term ID by name (case-insensitive, HTML-entity aware), slug, or numeric term_id
	 *
	 * @param string $candidate Term name, slug, or ID.
	 * @param string $taxonomy  Taxonomy slug.
	 * @return int              Term ID if found, 0 otherwise.
	 */
	private function find_single_term_id( $candidate, $taxonomy ) {
		$candidate = trim( (string) $candidate );
		if ( '' === $candidate ) {
			return 0;
		}

		// 1. Match by exact name.
		$term = get_term_by( 'name', $candidate, $taxonomy );
		if ( $term && ! is_wp_error( $term ) ) {
			return (int) $term->term_id;
		}

		// 2. Match by HTML-encoded name (WordPress stores '&' as '&amp;' in term names).
		$encoded_candidate = esc_html( $candidate );
		if ( $encoded_candidate !== $candidate ) {
			$term = get_term_by( 'name', $encoded_candidate, $taxonomy );
			if ( $term && ! is_wp_error( $term ) ) {
				return (int) $term->term_id;
			}
		}

		// 3. Match by slug.
		$term = get_term_by( 'slug', sanitize_title( $candidate ), $taxonomy );
		if ( $term && ! is_wp_error( $term ) ) {
			return (int) $term->term_id;
		}

		// 4. Match by numeric term ID.
		if ( ctype_digit( $candidate ) ) {
			$term = get_term_by( 'id', absint( $candidate ), $taxonomy );
			if ( $term && ! is_wp_error( $term ) ) {
				return (int) $term->term_id;
			}
		}

		return 0;
	}

	/**
	 * Retrieve valid Listeo listing types (slug => label)
	 *
	 * @return array
	 */
	private function get_valid_listing_types() {
		$options = array();

		if ( function_exists( 'listeo_core_custom_listing_types' ) ) {
			$custom_types_manager = listeo_core_custom_listing_types();
			if ( $custom_types_manager && method_exists( $custom_types_manager, 'get_listing_types' ) ) {
				$types = $custom_types_manager->get_listing_types( true );
				if ( ! empty( $types ) && is_array( $types ) ) {
					foreach ( $types as $type ) {
						if ( is_object( $type ) && ! empty( $type->slug ) ) {
							$options[ $type->slug ] = ! empty( $type->name ) ? $type->name : ucfirst( $type->slug );
						}
					}
				}
			}
		}

		if ( empty( $options ) ) {
			$options = array(
				'service'     => __( 'Service', 'listeo-bulk-add-listings' ),
				'rental'      => __( 'Rental', 'listeo-bulk-add-listings' ),
				'event'       => __( 'Event', 'listeo-bulk-add-listings' ),
				'classifieds' => __( 'Classifieds', 'listeo-bulk-add-listings' ),
			);
		}

		return $options;
	}

	/**
	 * Validate and resolve a listing type string against valid Listeo listing types (by slug or name)
	 *
	 * @param string $raw_type    Raw listing type cell value from CSV.
	 * @param array  $valid_types Associative array of valid listing types (slug => label).
	 * @return string|false       Canonical listing type slug or false if invalid.
	 */
	private function resolve_listing_type( $raw_type, $valid_types ) {
		$candidate = trim( (string) $raw_type );
		if ( '' === $candidate ) {
			return false;
		}

		$lower_candidate = strtolower( $candidate );
		$slug_candidate  = sanitize_key( str_replace( array( ' ', '-' ), '_', $lower_candidate ) );

		foreach ( $valid_types as $slug => $label ) {
			if ( strtolower( $slug ) === $lower_candidate || strtolower( $slug ) === $slug_candidate || strtolower( $label ) === $lower_candidate ) {
				return $slug;
			}
		}

		return false;
	}

	/**
	 * Build a case-insensitive lookup map of valid PHP timezone identifiers
	 *
	 * @return array
	 */
	private function get_valid_timezones_map() {
		$map = array();
		foreach ( DateTimeZone::listIdentifiers( DateTimeZone::ALL ) as $tz ) {
			$map[ strtolower( $tz ) ] = $tz;
		}
		$map['utc'] = 'UTC';
		return $map;
	}

	/**
	 * Validate and normalize a timezone string (supports IANA identifiers and WordPress UTC±offset format)
	 *
	 * @param string $raw_tz          User-supplied timezone string.
	 * @param array  $valid_timezones Lowercase-to-canonical map of PHP timezones.
	 * @return string|false           Canonical timezone string or false if invalid.
	 */
	private function validate_and_normalize_timezone( $raw_tz, $valid_timezones ) {
		$clean = trim( (string) $raw_tz );
		// Allow spaces in city names to be underscores (e.g. "America/New York" -> "America/New_York").
		$clean_underscore = str_replace( ' ', '_', $clean );
		$lower            = strtolower( $clean_underscore );

		if ( isset( $valid_timezones[ $lower ] ) ) {
			return $valid_timezones[ $lower ];
		}

		// Support WordPress UTC offsets like UTC+0, UTC-5, UTC+5:30, UTC+5.5.
		if ( preg_match( '/^UTC([+-]\d{1,2}(?::\d{2}|\.\d+)?)?$/i', $clean, $matches ) ) {
			return strtoupper( $clean );
		}

		return false;
	}

	/**
	 * Normalize and validate a URL string
	 *
	 * @param string $raw_url Raw URL string.
	 * @return string|false   Normalized URL or false if invalid.
	 */
	private function normalize_url( $raw_url ) {
		$url = trim( (string) $raw_url );
		if ( '' === $url ) {
			return '';
		}

		if ( ! preg_match( '#^https?://#i', $url ) ) {
			$url = 'https://' . ltrim( $url, '/' );
		}

		$sanitized = esc_url_raw( $url );
		if ( empty( $sanitized ) || ! filter_var( $sanitized, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		return $sanitized;
	}

	/**
	 * Detect CSV delimiter from the first line of the file
	 *
	 * @param resource $handle File pointer resource.
	 * @return string          Detected delimiter character.
	 */
	private function detect_csv_delimiter( $handle ) {
		$first_line = fgets( $handle );
		rewind( $handle );

		if ( false === $first_line ) {
			return ',';
		}

		$delimiters = array( ',', ';', "\t" );
		$counts     = array();
		foreach ( $delimiters as $d ) {
			$counts[ $d ] = substr_count( $first_line, $d );
		}

		arsort( $counts );
		$best = key( $counts );
		return $counts[ $best ] > 0 ? $best : ',';
	}

	/**
	 * Check whether a CSV row array contains only empty strings
	 *
	 * @param array $row CSV row array.
	 * @return bool
	 */
	private function is_row_empty( $row ) {
		if ( ! is_array( $row ) ) {
			return true;
		}
		foreach ( $row as $cell ) {
			if ( null !== $cell && '' !== trim( (string) $cell ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Store results in user transient and redirect back to the Bulk Add Listings admin page
	 *
	 * @param array $data Result payload.
	 */
	private function save_results_and_redirect( $data ) {
		$user_id = get_current_user_id();
		set_transient( self::TRANSIENT_PREFIX . $user_id, $data, 120 );

		$redirect_url = add_query_arg(
			array(
				'post_type'   => 'listing',
				'page'        => self::MENU_SLUG,
				'bulk_result' => '1',
			),
			admin_url( 'edit.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Render the Bulk Add Listings admin page
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'listeo-bulk-add-listings' ) );
		}

		$user_id = get_current_user_id();
		$results = get_transient( self::TRANSIENT_PREFIX . $user_id );
		if ( false !== $results ) {
			delete_transient( self::TRANSIENT_PREFIX . $user_id );
		}

		$selected_author = isset( $results['author_id'] ) && $results['author_id'] ? absint( $results['author_id'] ) : $user_id;

		$download_url = wp_nonce_url(
			add_query_arg(
				array(
					'post_type' => 'listing',
					'page'      => self::MENU_SLUG,
					'action'    => 'download_template',
				),
				admin_url( 'edit.php' )
			),
			'listeo_bulk_download_template',
			'template_nonce'
		);

		$users = get_users(
			array(
				'orderby' => 'display_name',
				'order'   => 'ASC',
				'fields'  => array( 'ID', 'user_login', 'display_name', 'user_email' ),
			)
		);

		$categories = get_terms(
			array(
				'taxonomy'   => 'listing_category',
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		$regions = get_terms(
			array(
				'taxonomy'   => 'region',
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		$listing_types = $this->get_valid_listing_types();
		$columns       = self::get_csv_columns();
		?>
		<style>
			.listeo-bulk-add-wrap {
				max-width: 1080px;
				margin-top: 20px;
			}
			.listeo-bulk-card {
				background: #fff;
				border: 1px solid #dcdcde;
				border-radius: 8px;
				padding: 24px 28px;
				margin-bottom: 24px;
				box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
			}
			.listeo-bulk-card h2 {
				margin-top: 0;
				font-size: 18px;
				padding-bottom: 12px;
				border-bottom: 1px solid #f0f0f1;
			}
			.listeo-bulk-grid {
				display: grid;
				grid-template-columns: 2fr 1fr;
				gap: 24px;
				align-items: start;
			}
			@media (max-width: 900px) {
				.listeo-bulk-grid {
					grid-template-columns: 1fr;
				}
			}
			.listeo-bulk-form-row {
				margin-bottom: 20px;
			}
			.listeo-bulk-form-row label {
				display: block;
				font-weight: 600;
				margin-bottom: 8px;
				color: #1d2327;
			}
			.listeo-bulk-form-row select,
			.listeo-bulk-form-row input[type="file"] {
				width: 100%;
				max-width: 480px;
				padding: 8px 10px;
				border: 1px solid #8c8f94;
				border-radius: 4px;
				background: #fff;
			}
			.listeo-bulk-form-row .description {
				color: #646970;
				font-size: 13px;
				margin-top: 6px;
			}
			.listeo-bulk-notice {
				border-radius: 6px;
				padding: 16px 20px;
				margin-bottom: 24px;
			}
			.listeo-bulk-notice.success {
				background: #edfaef;
				border-left: 4px solid #00a32a;
			}
			.listeo-bulk-notice.error {
				background: #fcf0f1;
				border-left: 4px solid #d63638;
			}
			.listeo-bulk-error-table,
			.listeo-bulk-columns-table {
				width: 100%;
				border-collapse: collapse;
				margin-top: 12px;
				font-size: 13px;
			}
			.listeo-bulk-error-table th,
			.listeo-bulk-error-table td,
			.listeo-bulk-columns-table th,
			.listeo-bulk-columns-table td {
				text-align: left;
				padding: 10px 12px;
				border-bottom: 1px solid #e5e7eb;
				vertical-align: top;
			}
			.listeo-bulk-error-table th,
			.listeo-bulk-columns-table th {
				background: #f8fafc;
				font-weight: 600;
			}
			.listeo-bulk-badge-req {
				display: inline-block;
				background: #fee2e2;
				color: #991b1b;
				font-size: 11px;
				font-weight: 600;
				padding: 2px 6px;
				border-radius: 4px;
			}
			.listeo-bulk-badge-opt {
				display: inline-block;
				background: #f3f4f6;
				color: #374151;
				font-size: 11px;
				padding: 2px 6px;
				border-radius: 4px;
			}
			.listeo-bulk-tag-list {
				max-height: 180px;
				overflow-y: auto;
				display: flex;
				flex-wrap: wrap;
				gap: 6px;
				padding: 4px 0;
			}
			.listeo-bulk-tag {
				background: #f0f6fc;
				border: 1px solid #c5d9ed;
				color: #1d2327;
				padding: 3px 8px;
				border-radius: 4px;
				font-size: 12px;
			}
		</style>

		<div class="wrap listeo-bulk-add-wrap">
			<h1><?php esc_html_e( 'Bulk Add Listings', 'listeo-bulk-add-listings' ); ?></h1>
			<p><?php esc_html_e( 'Upload a CSV template to bulk create pending listings in Listeo Core.', 'listeo-bulk-add-listings' ); ?></p>

			<?php if ( ! empty( $results ) ) : ?>
				<?php if ( ! empty( $results['errors'] ) ) : ?>
					<div class="listeo-bulk-notice error">
						<h3 style="margin-top: 0; color: #d63638;">
							<?php
							printf(
								/* translators: %d: number of validation errors */
								esc_html__( 'Upload rejected: %d syntax/validation error(s) found. Please fix the issues below and re-upload your CSV.', 'listeo-bulk-add-listings' ),
								count( $results['errors'] )
							);
							?>
						</h3>
						<table class="listeo-bulk-error-table">
							<thead>
								<tr>
									<th style="width: 80px;"><?php esc_html_e( 'CSV Row', 'listeo-bulk-add-listings' ); ?></th>
									<th style="width: 200px;"><?php esc_html_e( 'Listing Name', 'listeo-bulk-add-listings' ); ?></th>
									<th style="width: 150px;"><?php esc_html_e( 'Column', 'listeo-bulk-add-listings' ); ?></th>
									<th><?php esc_html_e( 'Error Details', 'listeo-bulk-add-listings' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $results['errors'] as $err ) : ?>
									<tr>
										<td><strong><?php echo esc_html( $err['row'] ); ?></strong></td>
										<td><?php echo esc_html( $err['listing'] ); ?></td>
										<td><code><?php echo esc_html( $err['column'] ); ?></code></td>
										<td><?php echo esc_html( $err['message'] ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $results['created'] ) ) : ?>
					<div class="listeo-bulk-notice success">
						<h3 style="margin-top: 0; color: #00a32a;">
							<?php
							printf(
								/* translators: %d: number of created pending listings */
								esc_html__( 'Success! %d pending listing(s) created.', 'listeo-bulk-add-listings' ),
								count( $results['created'] )
							);
							?>
						</h3>
						<ul style="margin: 8px 0 0 20px; list-style: disc;">
							<?php foreach ( $results['created'] as $item ) : ?>
								<li>
									<strong><?php echo esc_html( $item['title'] ); ?></strong>
									(<?php esc_html_e( 'Status: Pending', 'listeo-bulk-add-listings' ); ?>)
									<?php if ( ! empty( $item['edit_url'] ) ) : ?>
										— <a href="<?php echo esc_url( $item['edit_url'] ); ?>"><?php esc_html_e( 'Edit Listing', 'listeo-bulk-add-listings' ); ?></a>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<div class="listeo-bulk-grid">
				<div>
					<!-- Step 1: Download Example Template -->
					<div class="listeo-bulk-card">
						<h2><?php esc_html_e( '1. Download Example CSV Template', 'listeo-bulk-add-listings' ); ?></h2>
						<p><?php esc_html_e( 'Download a ready-to-use CSV template pre-configured with all required column headers and a sample row.', 'listeo-bulk-add-listings' ); ?></p>
						<p>
							<a href="<?php echo esc_url( $download_url ); ?>" class="button button-secondary button-large">
								<span class="dashicons dashicons-download" style="vertical-align: middle; margin-right: 4px;"></span>
								<?php esc_html_e( 'Download Example Template (.csv)', 'listeo-bulk-add-listings' ); ?>
							</a>
						</p>
					</div>

					<!-- Step 2: Upload Template & Select Author -->
					<div class="listeo-bulk-card">
						<h2><?php esc_html_e( '2. Upload CSV Template & Assign Author', 'listeo-bulk-add-listings' ); ?></h2>
						<form method="post" action="" enctype="multipart/form-data">
							<?php wp_nonce_field( 'listeo_bulk_add_listings_upload', 'listeo_bulk_add_nonce' ); ?>

							<div class="listeo-bulk-form-row">
								<label for="listing_author"><?php esc_html_e( 'Select Listing Author', 'listeo-bulk-add-listings' ); ?></label>
								<select name="listing_author" id="listing_author" required>
									<?php foreach ( $users as $u ) : ?>
										<option value="<?php echo esc_attr( $u->ID ); ?>" <?php selected( $selected_author, $u->ID ); ?>>
											<?php
											echo esc_html(
												sprintf(
													'%s (%s - %s)',
													$u->display_name,
													$u->user_login,
													$u->user_email
												)
											);
											?>
										</option>
									<?php endforeach; ?>
								</select>
								<p class="description">
									<?php esc_html_e( 'All listings created from this CSV upload will be assigned to the selected user and set to "Pending" status.', 'listeo-bulk-add-listings' ); ?>
								</p>
							</div>

							<div class="listeo-bulk-form-row">
								<label for="csv_file"><?php esc_html_e( 'CSV Template File', 'listeo-bulk-add-listings' ); ?></label>
								<input type="file" name="csv_file" id="csv_file" accept=".csv,text/csv" required />
								<p class="description">
									<?php esc_html_e( 'Select your completed .csv file. The file syntax, categories, regions, coordinates, email, and timezone will be validated before any listings are created.', 'listeo-bulk-add-listings' ); ?>
								</p>
							</div>

							<p style="margin-top: 24px;">
								<button type="submit" name="listeo_bulk_add_submit" class="button button-primary button-hero">
									<?php esc_html_e( 'Validate & Bulk Add Pending Listings', 'listeo-bulk-add-listings' ); ?>
								</button>
							</p>
						</form>
					</div>
				</div>

				<!-- Sidebar Reference: Valid Listing Types, Categories & Regions -->
				<div>
					<div class="listeo-bulk-card">
						<h2><?php esc_html_e( 'Valid Listing Types', 'listeo-bulk-add-listings' ); ?></h2>
						<p class="description" style="margin-bottom: 10px;">
							<?php esc_html_e( 'Values in the "Listing type" column must match one of these active listing types (by slug or name):', 'listeo-bulk-add-listings' ); ?>
						</p>
						<div class="listeo-bulk-tag-list">
							<?php foreach ( $listing_types as $type_slug => $type_label ) : ?>
								<span class="listeo-bulk-tag" title="<?php echo esc_attr( 'Slug: ' . $type_slug ); ?>">
									<?php echo esc_html( $type_label . ' (' . $type_slug . ')' ); ?>
								</span>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="listeo-bulk-card">
						<h2><?php esc_html_e( 'Existing Listing Categories', 'listeo-bulk-add-listings' ); ?></h2>
						<p class="description" style="margin-bottom: 10px;">
							<?php esc_html_e( 'Values in the "category" column must match one or more of these existing categories (by name, slug, or ID):', 'listeo-bulk-add-listings' ); ?>
						</p>
						<?php if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) : ?>
							<div class="listeo-bulk-tag-list">
								<?php foreach ( $categories as $cat ) : ?>
									<span class="listeo-bulk-tag" title="<?php echo esc_attr( 'Slug: ' . $cat->slug . ' | ID: ' . $cat->term_id ); ?>">
										<?php echo esc_html( $cat->name ); ?>
									</span>
								<?php endforeach; ?>
							</div>
						<?php else : ?>
							<p class="description" style="color: #d63638;">
								<?php esc_html_e( 'No categories found in Listing Categories. Please create at least one category before uploading.', 'listeo-bulk-add-listings' ); ?>
							</p>
						<?php endif; ?>
					</div>

					<div class="listeo-bulk-card">
						<h2><?php esc_html_e( 'Existing Regions', 'listeo-bulk-add-listings' ); ?></h2>
						<p class="description" style="margin-bottom: 10px;">
							<?php esc_html_e( 'If a value is provided in the "Region" column, it must match one or more of these existing regions:', 'listeo-bulk-add-listings' ); ?>
						</p>
						<?php if ( ! is_wp_error( $regions ) && ! empty( $regions ) ) : ?>
							<div class="listeo-bulk-tag-list">
								<?php foreach ( $regions as $reg ) : ?>
									<span class="listeo-bulk-tag" title="<?php echo esc_attr( 'Slug: ' . $reg->slug . ' | ID: ' . $reg->term_id ); ?>">
										<?php echo esc_html( $reg->name ); ?>
									</span>
								<?php endforeach; ?>
							</div>
						<?php else : ?>
							<p class="description">
								<?php esc_html_e( 'No regions currently exist in the Regions taxonomy.', 'listeo-bulk-add-listings' ); ?>
							</p>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- Column Reference Table -->
			<div class="listeo-bulk-card">
				<h2><?php esc_html_e( 'CSV Column Format & Field Mapping Reference', 'listeo-bulk-add-listings' ); ?></h2>
				<table class="listeo-bulk-columns-table">
					<thead>
						<tr>
							<th style="width: 180px;"><?php esc_html_e( 'CSV Column Header', 'listeo-bulk-add-listings' ); ?></th>
							<th style="width: 110px;"><?php esc_html_e( 'Required?', 'listeo-bulk-add-listings' ); ?></th>
							<th style="width: 260px;"><?php esc_html_e( 'Listeo Target Field / Taxonomy', 'listeo-bulk-add-listings' ); ?></th>
							<th><?php esc_html_e( 'Validation & Notes', 'listeo-bulk-add-listings' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $columns as $col ) : ?>
							<tr>
								<td><code><?php echo esc_html( $col['label'] ); ?></code></td>
								<td>
									<?php if ( $col['required'] ) : ?>
										<span class="listeo-bulk-badge-req"><?php esc_html_e( 'Required', 'listeo-bulk-add-listings' ); ?></span>
									<?php else : ?>
										<span class="listeo-bulk-badge-opt"><?php esc_html_e( 'Optional', 'listeo-bulk-add-listings' ); ?></span>
									<?php endif; ?>
								</td>
								<td><code><?php echo esc_html( $col['key'] ); ?></code></td>
								<td><?php echo esc_html( $col['description'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}
}

// Initialize the plugin.
Listeo_Bulk_Add_Listings::instance();
