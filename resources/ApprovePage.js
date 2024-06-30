const sendApprovalRequest = ( approve ) => {
	const restClient = new mw.Rest();
	const revisionId = mw.config.get( 'wgRevisionId' );
	const endpoint = `/page-approvals/v0/revision/${ revisionId }/${ approve ? 'approve' : 'unapprove' }`;

	restClient.post( endpoint )
		.then( ( data ) => {
			handleApprovalResponse( approve, data );
		} )
		.catch( error => {
			console.error( 'API request failed:', error );
			mw.notify( `API request failed: ${ error }`, { type: 'error' } );
		} );
};

const handleApprovalResponse = ( approve, data ) => {
	const elements = [ '#unapproveButton', '#approveButton', '.approved-status-text', '.tooltip', '.approver-details' ];
	const $badge = $( '.approval-status-badge' );

	$badge.toggleClass( 'approved-badge', approve );
	$badge.toggleClass( 'not-approved-badge', !approve );

	elements.forEach( selector => $( selector ).toggleClass( 'd-none' ) );
	$( '.approval-dropdown' ).removeClass( 'show' );

	if( approve ) {
		const { approver, approvalTimestamp } = data;
		$( '.approver-name' ).text( approver );
		convertTimestamps( approvalTimestamp );
		console.log( approver, approvalTimestamp );
	}
};

$( '#approveButton, #unapproveButton' ).click( function() {
	sendApprovalRequest( $( this ).is( '#approveButton' ) );
} );

$( '.approval-status-badge:not(.approval-dropdown)' ).click( e => {
	e.stopPropagation();
	$( '.approval-dropdown' ).toggleClass( 'show' );
} );

$( document ).click( e => {
	if( !$( e.target ).closest( '.approval-dropdown, .approval-status-badge' ).length ) {
		$( '.approval-dropdown' ).removeClass( 'show' );
	}
} );

const convertTimestamps = ( timestamp = null ) => {
	try {
		const moment = require( 'moment' );
		$( '.convert-timestamp' ).each( function() {
			const $element = $( this );
			const unixTimestamp = parseInt( timestamp || $element.attr( 'data-timestamp' ) );
			const momentDate = moment.unix( unixTimestamp ).utc();
			$element.text( momentDate.fromNow() );
		} );
	} catch( error ) {
		console.error( 'Error converting timestamps:', error );
	}
};

convertTimestamps();
