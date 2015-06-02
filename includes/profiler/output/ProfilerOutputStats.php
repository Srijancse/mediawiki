<?php
/**
 * ProfilerOutput class that flushes profiling data to the profiling
 * context's stats buffer.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Profiler
 */

/**
 * ProfilerOutput class that flushes profiling data to the profiling
 * context's stats buffer.
 *
 * @ingroup Profiler
 * @since 1.25
 */
class ProfilerOutputStats extends ProfilerOutput {

	/**
	 * Normalize a metric key for StatsD
	 *
	 * Replace occurences of '::' with dots and any other non-alphabetic
	 * characters with underscores. Combine runs of dots or underscores.
	 * Then trim leading or trailing dots or underscores.
	 *
	 * @param string $key
	 * @since 1.26
	 */
	private static function normalizeMetricKey( $key ) {
		$key = preg_replace( '/[:.]+/', '.', $key );
		$key = preg_replace( '/[^a-z.]+/i', '_', $key );
		$key = trim( $key, '_.' );
		return str_replace( array( '._', '_.' ), '.', $key );
	}

	/**
	 * Flush profiling data to the current profiling context's stats buffer.
	 *
	 * @param array $stats
	 */
	public function log( array $stats ) {
		if ( isset( $this->params['prefix'] ) ) {
			$prefix = self::normalizeMetricKey( $this->params['prefix'] );
		} else {
			$prefix = '';
		}

		$contextStats = $this->collector->getContext()->getStats();

		foreach ( $stats as $stat ) {
			$key = self::normalizeMetricKey( "{$prefix}.{$stat['name']}" );

			// Convert fractional seconds to whole milliseconds
			$cpu = round( $stat['cpu'] * 1000 );
			$real = round( $stat['real'] * 1000 );

			$contextStats->increment( "{$key}.calls" );
			$contextStats->timing( "{$key}.cpu", $cpu );
			$contextStats->timing( "{$key}.real", $real );
		}
	}
}