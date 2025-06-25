<template>
	<approval-button v-if="canApprove"></approval-button>
	<status-display
		v-else
		:page-approved="pageApproved"
		:approval-timestamp="approvalTimestamp"
		:approver="approver"
	></status-display>
</template>

<script>
const { defineComponent, inject } = require( 'vue' );
const ApprovalButton = require( './ApprovalButton.vue' );
const StatusDisplay = require( './StatusDisplay.vue' );

module.exports = defineComponent( {
	name: 'PageApprovals',
	components: { ApprovalButton, StatusDisplay },
	setup() {
		const {
			canApprove,
			pageApproved,
			approvalTimestamp,
			approver
		} = inject( 'props' );

		return {
			canApprove,
			pageApproved,
			approvalTimestamp,
			approver
		};
	}
} );
</script>
