const Vue = require( 'vue' );
const App = require( './components/App.vue' );

/**
 * Map of alternative target elements to mount the wrapper on
 * Key is the skin name, value is the target element selector
 */
const mountTargetOverrides = new Map( [
	[ 'vector-2022', '.vector-page-titlebar' ], // Similar to Language button
	[ 'citizen', '.page-actions' ]
] );

/**
 * Initialize the app
 *
 * @return {void}
 */
function initApp() {
	const wrapper = document.getElementById( 'ext-pageapprovals' );

	if ( !wrapper ) {
		return;
	}

	repositionWrapper( wrapper );

	const app = Vue.createMwApp( App );
	app.provide( 'props', {
		pageApproved: wrapper.dataset.mwPageApproved === 'true',
		canApprove: wrapper.dataset.mwCanApprove === 'true',
		approver: wrapper.dataset.mwApprover,
		approvalTimestamp: wrapper.dataset.mwApprovalTimestamp
	} );
	app.mount( wrapper );
}

/**
 * Reposition the wrapper if a skin-specific mount target is defined.
 *
 * @param {HTMLElement} wrapper
 * @return {void}
 */
function repositionWrapper( wrapper ) {
	const mountTargetOverride = mountTargetOverrides.get( mw.config.get( 'skin' ) );
	if ( mountTargetOverride !== undefined ) {
		const targetElement = document.querySelector( mountTargetOverride );
		if ( targetElement ) {
			const parentIndicator = wrapper.parentElement;
			targetElement.appendChild( wrapper );
			parentIndicator.remove();
		}
	}
}

initApp();
