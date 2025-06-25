/**
 * Get the relative time parts for a given timestamp
 *
 * @param {number} diffInSeconds
 * @return {Object}
 */
function getRelativeTimeParts( diffInSeconds ) {
	const MINUTE = 60;
	const HOUR = 60 * MINUTE;
	const DAY = 24 * HOUR;
	const WEEK = 7 * DAY;
	const MONTH = 30 * DAY;
	const YEAR = 365 * DAY;

	const absDiff = Math.abs( diffInSeconds );

	if ( absDiff < MINUTE ) {
		return { value: Math.round( diffInSeconds ), unit: 'second' };
	}
	if ( absDiff < HOUR ) {
		return { value: Math.round( diffInSeconds / MINUTE ), unit: 'minute' };
	}
	if ( absDiff < DAY ) {
		return { value: Math.round( diffInSeconds / HOUR ), unit: 'hour' };
	}
	if ( absDiff < WEEK * 4 ) { // Show days for up to 4 weeks
		return { value: Math.round( diffInSeconds / DAY ), unit: 'day' };
	}
	if ( absDiff < YEAR ) {
		return { value: Math.round( diffInSeconds / MONTH ), unit: 'month' };
	}
	return { value: Math.round( diffInSeconds / YEAR ), unit: 'year' };
}

/**
 * Check if the Intl.RelativeTimeFormat API is supported
 *
 * @return {boolean}
 */
function isIntlRelativeTimeFormatSupported() {
	return typeof Intl !== 'undefined' && 'RelativeTimeFormat' in Intl;
}

/**
 * Get the relative timestamp for a given timestamp
 *
 * @param {number} timestamp
 * @return {string|null}
 */
function getRelativeTimestamp( timestamp ) {
	if ( !timestamp || !isIntlRelativeTimeFormatSupported() ) {
		return null;
	}

	const then = new Date( timestamp );
	if ( isNaN( then.getTime() ) ) {
		return null;
	}

	/* eslint-disable-next-line compat/compat */
	const rtf = new Intl.RelativeTimeFormat(
		mw.config.get( 'wgUserLanguage' ),
		{ numeric: 'auto' }
	);

	const now = new Date();
	const diffInSeconds = ( then.getTime() - now.getTime() ) / 1000;

	const { value, unit } = getRelativeTimeParts( diffInSeconds );
	return rtf.format( value, unit );
}

/**
 * Check if the Intl.DateTimeFormat API is supported
 *
 * @return {boolean}
 */
function isIntlDateTimeFormatSupported() {
	return typeof Intl !== 'undefined' && 'DateTimeFormat' in Intl;
}

/**
 * Get the localized date and time for a given timestamp
 *
 * @param {number} timestamp
 * @return {string|null}
 */
function getLocalizedDateTime( timestamp ) {
	if ( !timestamp || !isIntlDateTimeFormatSupported() ) {
		return null;
	}
	const date = new Date( timestamp );
	if ( isNaN( date.getTime() ) ) {
		return null;
	}
	const dtf = new Intl.DateTimeFormat(
		mw.config.get( 'wgUserLanguage' ),
		{
			year: 'numeric',
			month: 'long',
			day: 'numeric',
			hour: 'numeric',
			minute: 'numeric'
		}
	);
	return dtf.format( date );
}

module.exports = {
	getRelativeTimestamp,
	getLocalizedDateTime
};
