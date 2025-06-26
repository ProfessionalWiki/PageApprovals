<template>
	<cdx-menu-button
		v-model:selected="selection"
		:menu-items="menuItems"
		@update:selected="onSelect"
	>
		<status-display
			:page-approved="pageApproved"
			:approval-timestamp="approvalTimestamp"
			:approver="approver"
		></status-display>
		<cdx-icon
			:icon="cdxIconExpand"
			size="x-small"
		></cdx-icon>
	</cdx-menu-button>
</template>

<script>
const { defineComponent, ref, computed, watch, inject } = require( 'vue' );
const { CdxMenuButton, CdxIcon } = require( '../../codex.js' );
const { cdxIconCheck, cdxIconClose, cdxIconExpand } = require( '../icons.json' );
const { setPageApprovalStatus } = require( '../utils/api.js' );
const StatusDisplay = require( './StatusDisplay.vue' );

module.exports = defineComponent( {
	name: 'ApprovalButton',
	components: { CdxMenuButton, CdxIcon, StatusDisplay },
	setup() {
		const selection = ref( null );
		const props = inject( 'props' );

		const pageApproved = ref( props.pageApproved );
		const approvalTimestamp = ref( props.approvalTimestamp );
		const approver = ref( props.approver );

		const rootEl = document.getElementById( 'ext-pageapprovals' );

		const menuItems = computed( () => {
			if ( pageApproved.value ) {
				return [ {
					label: mw.msg( 'pageapprovals-unapprove-button' ),
					icon: cdxIconClose,
					value: 'unapprove'
				} ];
			} else {
				return [ {
					label: mw.msg( 'pageapprovals-approve-button' ),
					icon: cdxIconCheck,
					value: 'approve'
				} ];
			}
		} );

		watch( pageApproved, ( newValue ) => {
			rootEl.dataset.mwPageApproved = newValue ? 'true' : 'false';
		} );
		watch( approvalTimestamp, ( newValue ) => {
			rootEl.dataset.mwApprovalTimestamp = newValue;
		} );
		watch( approver, ( newValue ) => {
			rootEl.dataset.mwApprover = newValue;
		} );

		// eslint-disable-next-line es-x/no-async-functions
		async function onSelect( value ) {
			if ( value === 'approve' ) {
				await setPageApprovalStatus( true );
				/**
				 * FIXME: Replace with the timestamp returned by the API
				 * The current timestamp is not the actual approval timestamp
				 * The API should return a ISO 8601 timestamp instead of relative time
				 */
				approvalTimestamp.value = new Date().toISOString();
				approver.value = mw.config.get( 'wgUserName' );
				pageApproved.value = true;
				mw.notify( mw.msg( 'pageapprovals-approved' ), { type: 'success', tag: 'pageapprovals' } );
			} else if ( value === 'unapprove' ) {
				await setPageApprovalStatus( false );
				approvalTimestamp.value = null;
				approver.value = null;
				pageApproved.value = false;
				mw.notify( mw.msg( 'pageapprovals-unapproved' ), { type: 'success', tag: 'pageapprovals' } );
			}
		}

		return {
			selection,
			menuItems,
			pageApproved,
			approvalTimestamp,
			approver,
			onSelect,
			cdxIconExpand
		};
	}
} );
</script>

<style lang="less">
.ext-pageapprovals {
	.cdx-toggle-button .cdx-tooltip {
		white-space: normal; // Fix incorrect wrapping in tooltip inheriting from the menu button
	}
}
</style>
