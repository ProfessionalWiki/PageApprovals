function sendApprovalRequest( approve ) {
	const pageId = mw.config.get( 'wgArticleId' );
	const endpoint = '/rest.php/page-approvals/v0/page/' + pageId + '/' + (approve ? 'approve' : 'unapprove');

	$.ajax( {
		url: endpoint,
		type: 'POST',
		contentType: 'application/json',
		success: () => {
			const message = approve ? 'Page approved' : 'Page unapproved';
			const statusMessage = approve ? mw.message( 'pageapprovals-status-approved' ).text() : mw.message(
				'pageapprovals-status-not-approved' ).text();
			$( '.page-approval-status' ).text( statusMessage );
			mw.notify( message, { type: 'success' } );

			$( '#approveButton' ).toggle( !approve );
			$( '#unapproveButton' ).toggle( approve );
		},
		error: function( xhr, status, error ) {
			mw.notify( 'API request failed: ' + error, { type: 'error' } );
		}
	} );
}

$( document ).on( 'click', '#approveButton, #unapproveButton', function() {
	const approve = $( this ).is( '#approveButton' );
	sendApprovalRequest( approve );
} );
