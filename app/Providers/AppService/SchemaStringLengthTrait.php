<?php
/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

namespace App\Providers\AppService;

use Illuminate\Support\Facades\Schema;

trait SchemaStringLengthTrait
{
	/**
	 * Set the default schema string length, avoiding the errors:
	 * - Specified key was too long error.
	 * - Index column size too large. The maximum column size is 767 bytes.
	 *
	 * @return void
	 */
	private function setDefaultSchemaStringLength(): void
	{
		/*
		 * The error "Index column size too large. The maximum column size is 767 bytes."
		 * typically occurs when you try to create an index on a column (usually a string column like VARCHAR or TEXT)
		 * that exceeds the maximum allowed size for indexed columns in MySQL,
		 * especially when using the utf8 or utf8mb4 character sets.
		 *
		 * Solutions to fix the error:
		 * 1. Change the column length
		 * One common approach is to limit the length of the indexed string column
		 * to ensure that the index size doesn't exceed the 767-byte limit.
		 * Example for VARCHAR column: $table->string('email', 191)->index(); // Limit the length to 191 characters
		 *
		 * For utf8mb4 character encoding, a maximum of 191 characters should be indexed,
		 * because utf8mb4 uses up to 4 bytes per character (191 * 4 = 764 bytes, within the 767-byte limit).
		 *
		 * For utf8 encoding (which uses 3 bytes per character), you could technically go up to 255 characters,
		 * but 191 is a safe default across character sets.
		 *
		 * 2. Use InnoDB with innodb_large_prefix
		 * If you are using the InnoDB storage engine, you can increase the index length limit
		 * by enabling innodb_large_prefix in your MySQL configuration.
		 * To enable innodb_large_prefix, ensure the following settings in your MySQL configuration (my.cnf or my.ini):
		 * [mysqld]
		 * innodb_file_format = Barracuda
		 * innodb_file_per_table = 1
		 * innodb_large_prefix = 1
		 *
		 * 3. Use a Prefix Index
		 * If you don’t need the full length of a string column to be indexed, you can create a prefix index,
		 * which indexes only the first N characters of a string.
		 * Example: $table->string('email')->index('email_index', 191); // Index the first 191 characters
		 *
		 * 4. Switch to MySQL 5.7+ or MariaDB 10.2+
		 * tarting with MySQL 5.7.7 and MariaDB 10.2, the default row format for InnoDB tables is DYNAMIC,
		 * and innodb_large_prefix is enabled by default, so the 767-byte limit is raised to 3072 bytes for indexed columns.
		 * If you're using an older version of MySQL, upgrading could automatically resolve this issue.
		 *
		 * 5. Check your MySQL Charset
		 * If you don't need the utf8mb4 character set, you can switch to utf8 (which supports 3-byte characters)
		 * to avoid exceeding the 767-byte limit. Example: $table->string('email')->charset('utf8')->index();
		 */
		try {
			Schema::defaultStringLength(191);
		} catch (\Throwable $e) {
		}
	}
}
