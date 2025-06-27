/**
 * Set the approval status of a page by sending a request to the API
 *
 * @param {boolean} approve
 * @return {Promise<Object>}
 */
function setPageApprovalStatus( approve ) {
	const restClient = new mw.Rest();
	const revisionId = mw.config.get( 'wgRevisionId' );
	const endpoint = `/page-approvals/v0/revision/${ revisionId }/${ approve ? 'approve' : 'unapprove' }`;

	return restClient.post( endpoint )
		.then( ( data ) => data )
		.catch( ( error, data ) => {
			mw.log.error( `API request failed: ${ error }` );
			mw.notify( data.xhr.responseJSON.message || `API request failed: ${ error }`, { type: 'error' } );
		} );
}

module.exports = {
	setPageApprovalStatus
};
