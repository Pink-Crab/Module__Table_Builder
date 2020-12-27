<?php

declare(strict_types=1);

/**
 * Tests the Table_Schema class
 *
 * @since 0.2.0
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @package PinkCrab\Core
 */

namespace PinkCrab\Modules\Registerables\Tests;

use WP_UnitTestCase;
use PinkCrab\Modules\Table_Builder\Table_Index;
use PinkCrab\Modules\Table_Builder\Table_Schema;

class Test_Table_Schema extends WP_UnitTestCase {



	/**
	 * WPDB
	 *
	 * @var wpdb
	 */
	protected $wpdb;

	/**
	 * Ensure we have wpdb instance.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setup();

		if ( ! $this->wpdb ) {
			global $wpdb;
			$this->wpdb = $wpdb;
		}
	}

	/**
	 * Test can create instance using static constructor.
	 *
	 * @return void
	 */
	public function test_can_create_with_static(): void {
		$this->assertInstanceOf( Table_Schema::class, Table_Schema::create( 'test' ) );
	}

	/**
	 * Test can set the table name when constructing with new.
	 *
	 * @return void
	 */
	public function test_can_get_set_table_name(): void {
		$schema = new Table_Schema();
		$schema->table( 'test' );
		$this->assertEquals( 'test', $schema->get_table_name() );
	}


	/**
	 * Test that the primary key can be retireived.
	 *
	 * @return void
	 */
	public function test_get_primary_key(): void {
		$schema = Table_Schema::create( 'test' )->primary( 'foo' );
		$this->assertEquals( 'foo', $schema->get_primary_key() );
	}

	/**
	 * Test that the columns can be retireived.
	 *
	 * @return void
	 */
	public function test_get_columns(): void {
		$schema = Table_Schema::create( 'test' )->column( 'foo' );
		$this->assertArrayHasKey( 'foo', $schema->get_columns() );
	}

	/**
	 * Test can get indexes.
	 *
	 * @return void
	 */
	public function test_can_get_index(): void {
		$index  = Table_Index::name( 'test' );
		$schema = Table_Schema::create( 'test' )->index( $index );
		$this->assertFalse( empty( $schema->get_indexes() ) );
		$this->assertEquals( $index, $schema->get_indexes()[0] );
	}

	/**
	 * Tests column properties.
	 *
	 * @return void
	 */
	public function test_can_add_colum_properties(): void {
		$schema  = Table_Schema::create( 'test' )
			->column( 'key' )
				->nullable()
				->unsigned( false )
				->default( 'default val' )
				->type( 'varchar' )
				->length( 255 )
				->auto_increment();
		$columns = $schema->get_columns();

		$this->assertArrayHasKey( 'key', $columns );
		$this->assertArrayHasKey( 'default', $columns['key'] );
		$this->assertEquals( 'key', $columns['key']['key'] );
		$this->assertEquals( 255, $columns['key']['length'] );
		$this->assertEquals( 'default val', $columns['key']['default'] );
		$this->assertTrue( $columns['key']['auto_increment'] );
		$this->assertFalse( $columns['key']['unsigned'] );
		$this->assertTrue( $columns['key']['null'] );
	}

	/**
	 * Ensure null() is now Deprecated and thorws error.
	 *
	 * @return void
	 */
	public function test_null_is_deprecated(): void {
		$schema = new Table_Schema( 'test' );
		try {
			$schema->column( 'test' )->null();
		} catch ( \Throwable $th ) {
			$this->assertTrue(
				str_contains(
					$th->getMessage(),
					'null is deprecated, pleae use nullable(bool)'
				)
			);
		}
	}

	/**
	 * Ensure the int(), text(), float()....
	 *
	 * @return void
	 */
	public function test_type_shortcuts(): void {
		$schema = new Table_Schema( 'test' );
		$schema->column( 'int' )->int( 10 );
		$schema->column( 'float' )->float();
		$schema->column( 'double' )->double( 8 );
		$schema->column( 'varchar' )->varchar( 256 );
		$schema->column( 'datetime' )->datetime( 'CURRENT_TIMESTAMP' );
		$schema->column( 'timestamp' )->timestamp();

		// Get the columns
		$columns = \_getPrivateProperty( $schema, 'columns' );

		// Int
		$this->assertEquals( 'int', $columns['int']['type'] );
		$this->assertEquals( 10, $columns['int']['length'] );

		// FLoat
		$this->assertEquals( 'float', $columns['float']['type'] );
		$this->assertEquals( null, $columns['float']['length'] );

		// Double
		$this->assertEquals( 'double', $columns['double']['type'] );
		$this->assertEquals( 8, $columns['double']['length'] );

		// varchar
		$this->assertEquals( 'varchar', $columns['varchar']['type'] );
		$this->assertEquals( 256, $columns['varchar']['length'] );

		// Datetime
		$this->assertEquals( 'datetime', $columns['datetime']['type'] );
		$this->assertEquals( 'CURRENT_TIMESTAMP', $columns['datetime']['default'] );

		$this->assertEquals( 'timestamp', $columns['timestamp']['type'] );
		$this->assertArrayNotHasKey( 'default', $columns['timestamp'] );
	}
}
