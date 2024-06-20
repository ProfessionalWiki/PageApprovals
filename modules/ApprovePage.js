const restClient = new mw.Rest();

function sendApprovalRequest( pageId, approve ) {
	const endpoint = '/page-approvals/v0/page/' + pageId + '/' + (approve ? 'approve' : 'unapprove');
	const csrfToken = mw.user.tokens.values.csrfToken;

	restClient.post( endpoint, {
		token: csrfToken
	} ).then( function( response ) {
		console.log( 'Request successful:', response );
		mw.notify( 'Request successful: ' + JSON.stringify( response ) );
	} ).catch( function( error ) {
		console.error( 'API request failed:', error );
		mw.notify( 'API request failed: ' + error, { type: 'error' } );
	} );
}

$( document ).on( 'click', '#approveButton', function() {
	const pageId = $( this ).data( 'page-id' );
	sendApprovalRequest( pageId, true );
} );

$( document ).on( 'click', '#unapproveButton', function() {
	const pageId = $( this ).data( 'page-id' );
	sendApprovalRequest( pageId, false );
} );
