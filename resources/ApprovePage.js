const restClient = new mw.Rest();

function sendApprovalRequest( approve ) {
	const revisionId = mw.config.get( 'wgRevisionId' );
	const endpoint = `/page-approvals/v0/revision/${ revisionId }/${ approve ? 'approve' : 'unapprove' }`;

	restClient.post( endpoint )
		.then( response => handleApprovalResponse( approve ) )
		.catch( error => {
			console.error( 'API request failed:', error );
			mw.notify( 'API request failed: ' + error, { type: 'error' } );
		} );
}

function handleApprovalResponse( approve ) {
	const message = approve ? 'Page approved' : 'Page unapproved';
	const statusMessage = mw.message( approve ? 'pageapprovals-status-approved' : 'pageapprovals-status-not-approved' ).text();
	$( '.page-approval-status' ).text( statusMessage );
	mw.notify( message, { type: 'success' } );

	$( '#approveButton' ).toggle( !approve );
	$( '#unapproveButton' ).toggle( approve );
}

$( '#approveButton, #unapproveButton' ).click( function() {
	const approve = $( this ).is( '#approveButton' );
	sendApprovalRequest( approve );
} );
