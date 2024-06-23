const restClient = new mw.Rest();

function sendApprovalRequest( approve ) {
	const pageId = mw.config.get( 'wgArticleId' );
	const endpoint = `/page-approvals/v0/page/${ pageId }/${ approve ? 'approve' : 'unapprove' }`;
	const csrfToken = mw.user.tokens.values.csrfToken;

	restClient.post( endpoint, { token: csrfToken } )
		.then( response => {
			console.log( 'Request successful:', response );
			mw.notify( 'Request successful: ' + JSON.stringify( response ) );
		} )
		.catch( error => {
			console.error( 'API request failed:', error );
			mw.notify( 'API request failed: ' + error, { type: 'error' } );
		} );
}

$( '#approveButton, #unapproveButton' ).click( function() {
	const approve = $( this ).is( '#approveButton' );
	sendApprovalRequest( approve );
} );
